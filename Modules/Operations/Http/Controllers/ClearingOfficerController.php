<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\Operations\Entities\ClearingAssignment;
use Modules\Operations\Entities\ClearingOfficer;
use Modules\Operations\Entities\ClearingWork;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Services\ClearingLedger;

/**
 * Clearing officers: who they are, which clearings they hold or held, and
 * the work credited to them. Assigning, reassigning and moving credit all
 * go through ClearingLedger so they land on the audit trail.
 */
class ClearingOfficerController extends Controller
{
    public function __construct(private ClearingLedger $ledger)
    {
    }

    public function index(Request $request)
    {
        $officers = ClearingOfficer::query()
            ->withCount(['currentAssignments', 'assignments', 'work'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->input('search'));
                $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('company', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('licence_number', 'like', "%{$s}%"));
            })
            ->when($request->input('show') !== 'all', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')->get();

        return Inertia::render('Operations/ClearingOfficers/Index', [
            'officers' => $officers,
            'filters' => $request->only(['search', 'show']),
            'unassigned' => $this->unassigned(),
            'recentChanges' => \Spatie\Activitylog\Models\Activity::where('log_name', ClearingLedger::LOG)
                ->whereIn('event', ['reassigned', 'credit_overridden'])
                ->with('causer', 'subject')->latest('id')->limit(30)->get()
                ->map(fn ($a) => ClearingLedger::auditRow($a) + ['link' => $this->linkFor($a->subject)]),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(ClearingOfficer $officer)
    {
        $officer->load('user:id,name');
        $assignments = $officer->assignments()->with(['clearable', 'assigner:id,name', 'ender:id,name'])->get()
            ->map(fn (ClearingAssignment $a) => [
                'id' => $a->id,
                'label' => ClearingLedger::label($a->clearable),
                'link' => $this->linkFor($a->clearable),
                'started_at' => $a->started_at?->toIso8601String(),
                'ended_at' => $a->ended_at?->toIso8601String(),
                'assigned_by' => $a->assigner?->name,
                'ended_by' => $a->ender?->name,
                'reason' => $a->reason,
                'status' => $a->clearable?->status_label,
            ]);

        $work = $officer->work()->with(['clearable', 'user:id,name', 'originalOfficer:id,name', 'overrider:id,name'])->limit(300)->get()
            ->map(fn (ClearingWork $w) => $this->workRow($w));

        // Work this officer did that an override moved to someone else.
        $movedAway = ClearingWork::where('original_officer_id', $officer->id)->where('clearing_officer_id', '!=', $officer->id)
            ->with(['clearable', 'officer:id,name', 'overrider:id,name'])->latest('overridden_at')->limit(100)->get()
            ->map(fn (ClearingWork $w) => $this->workRow($w));

        return Inertia::render('Operations/ClearingOfficers/Show', [
            'officer' => $officer,
            'assignments' => $assignments,
            'work' => $work,
            'movedAway' => $movedAway,
            'counts' => [
                'current' => $assignments->whereNull('ended_at')->count(),
                'handled' => $assignments->pluck('label')->unique()->count(),
                'work' => $work->count(),
            ],
            'actions' => ClearingWork::ACTIONS,
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $officer = ClearingOfficer::create($this->validated($request) + ['created_by' => $request->user()->id]);
        activity(ClearingLedger::LOG)->performedOn($officer)->causedBy($request->user())->event('created')->log("Added clearing officer {$officer->name}");

        if ($request->boolean('redirect_back')) {
            return redirect()->back()->with('success', "{$officer->name} added.");
        }

        return redirect()->route('operations.clearing-officers.show', $officer)->with('success', "{$officer->name} added.");
    }

    public function update(Request $request, ClearingOfficer $officer)
    {
        $data = $this->validated($request, $officer);
        $old = $officer->only(array_keys($data));
        $officer->update($data);
        $changed = collect($data)->filter(fn ($v, $k) => (string) ($old[$k] ?? '') !== (string) $v)->keys();
        if ($changed->isNotEmpty()) {
            activity(ClearingLedger::LOG)->performedOn($officer)->causedBy($request->user())->event('updated')
                ->withProperties(['changed' => $changed->values()])->log("Updated clearing officer {$officer->name}");
        }

        return redirect()->back()->with('success', 'Clearing officer updated.');
    }

    /**
     * Assign or reassign the officer on a car container or clearing job.
     */
    public function assign(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'clearable_type' => 'required|in:container,job',
            'clearable_id' => 'required|integer',
            'clearing_officer_id' => ['required', Rule::exists('clearing_officers', 'id')->whereNull('deleted_at')],
            'reason' => 'nullable|string|max:500',
            'move_earlier_work' => 'boolean',
            'override_reason' => 'nullable|string|max:500',
        ], ['clearing_officer_id.required' => 'Pick a clearing officer.']);

        $clearable = $this->findClearable($data['clearable_type'], (int) $data['clearable_id']);
        $officer = ClearingOfficer::findOrFail($data['clearing_officer_id']);
        $current = $this->ledger->current($clearable);
        $move = (bool) ($data['move_earlier_work'] ?? false);

        if ($current && empty($data['reason'])) {
            throw ValidationException::withMessages(['reason' => 'Say why it is being reassigned; it goes on the audit trail.']);
        }
        if ($move) {
            abort_unless($user->can('clearing.override'), 403, 'Only the CEO or Head of Business can move credit for earlier work.');
            if (empty($data['override_reason'])) {
                throw ValidationException::withMessages(['override_reason' => 'Say why earlier work should be credited to the new officer.']);
            }
        }
        if (!$officer->is_active) {
            throw ValidationException::withMessages(['clearing_officer_id' => "{$officer->name} is marked inactive."]);
        }

        $this->ledger->assign($clearable, $officer, $user, $data['reason'] ?? null, $move, $data['override_reason'] ?? null);

        return redirect()->back()->with('success', $current
            ? "Reassigned to {$officer->name}." . ($move ? ' Earlier work moved to them too.' : ' Earlier work stays with ' . $current->officer?->name . '.')
            : "Assigned to {$officer->name}.");
    }

    /** Log a piece of work by hand (e.g. "collected DO", "attended examination"). */
    public function logWork(Request $request)
    {
        $data = $request->validate([
            'clearable_type' => 'required|in:container,job',
            'clearable_id' => 'required|integer',
            'description' => 'required|string|max:500',
            'occurred_at' => 'nullable|date|before_or_equal:now',
        ], ['description.required' => 'Say what was done.']);

        $clearable = $this->findClearable($data['clearable_type'], (int) $data['clearable_id']);
        $work = $this->ledger->record($clearable, 'note', $data['description'], null, $request->user(), $data['occurred_at'] ?? null);
        if (!$work) {
            throw ValidationException::withMessages(['description' => 'Assign a clearing officer first, so the work has someone to be credited to.']);
        }

        return redirect()->back()->with('success', "Work logged for {$work->officer->name}.");
    }

    /**
     * Move credit for one piece of work, or all of an officer's work on a
     * clearing, to another officer. Needs a reason.
     */
    public function override(Request $request)
    {
        $data = $request->validate([
            'work_ids' => 'required|array|min:1',
            'work_ids.*' => 'integer|exists:clearing_work,id',
            'clearing_officer_id' => ['required', Rule::exists('clearing_officers', 'id')->whereNull('deleted_at')],
            'reason' => 'required|string|max:500',
        ], ['reason.required' => 'Give a reason for moving the credit; it goes on the audit trail.']);

        $work = ClearingWork::whereIn('id', $data['work_ids'])->with('officer', 'clearable')->get();
        if ($work->map(fn ($w) => $w->clearable_type . ':' . $w->clearable_id)->unique()->count() > 1) {
            throw ValidationException::withMessages(['work_ids' => 'Move credit for one clearing at a time.']);
        }
        $to = ClearingOfficer::findOrFail($data['clearing_officer_id']);
        $count = $this->ledger->override($work, $to, $request->user(), $data['reason']);

        return redirect()->back()->with('success', $count ? "Credit for {$count} " . ($count === 1 ? 'item' : 'items') . " moved to {$to->name}." : 'Nothing to move.');
    }

    // ---- helpers --------------------------------------------------------

    private function findClearable(string $type, int $id): Model
    {
        if ($type === 'job') {
            $job = Shipment::findOrFail($id);
            abort_unless($job->isGeneralCargo(), 404);

            return $job;
        }
        $container = Container::findOrFail($id);

        // A clearing job's containers are cleared together, under the job.
        return ClearingLedger::clearableFor($container);
    }

    private function linkFor(?Model $clearable): ?string
    {
        return match (true) {
            $clearable instanceof Container => route('operations.containers.show', $clearable->id),
            $clearable instanceof Shipment => route('operations.clearing.show', $clearable->id),
            $clearable instanceof ClearingOfficer => route('operations.clearing-officers.show', $clearable->id),
            default => null,
        };
    }

    private function workRow(ClearingWork $w): array
    {
        return [
            'id' => $w->id,
            'action' => $w->action,
            'description' => $w->description,
            'occurred_at' => $w->occurred_at?->toIso8601String(),
            'clearing' => ClearingLedger::label($w->clearable),
            'link' => $this->linkFor($w->clearable),
            'recorded_by' => $w->user?->name,
            'officer' => $w->officer?->name,
            'original_officer' => $w->originalOfficer?->name,
            'overridden_by' => $w->overrider?->name,
            'overridden_at' => $w->overridden_at?->toIso8601String(),
            'override_reason' => $w->override_reason,
        ];
    }

    /** Arrived car containers and open clearing jobs with no officer yet. */
    private function unassigned(): array
    {
        $assigned = fn (string $type) => ClearingAssignment::where('clearable_type', $type)->whereNull('ended_at')->pluck('clearable_id');

        $containers = Container::forVehicles()
            ->whereIn('status', [Container::STATUS_ARRIVED, Container::STATUS_OFFLOADED])
            ->whereNotIn('id', $assigned((new Container)->getMorphClass()))
            ->orderBy('arrived_on')->limit(50)->get(['id', 'container_number', 'status', 'arrived_on'])
            ->map(fn ($c) => ['label' => "Container {$c->container_number}", 'status' => $c->status_label, 'link' => route('operations.containers.show', $c->id)]);

        $jobs = Shipment::generalCargo()
            ->whereNotIn('status', [Shipment::STATUS_DELIVERED, Shipment::STATUS_EMPTY_RETURNED])
            ->whereNotIn('id', $assigned((new Shipment)->getMorphClass()))
            ->orderByDesc('id')->limit(50)->get(['id', 'tracking_id', 'cargo_description', 'status', 'cargo_type'])
            ->map(fn ($j) => ['label' => "{$j->tracking_id} · {$j->cargo_description}", 'status' => $j->status_label, 'link' => route('operations.clearing.show', $j->id)]);

        return $containers->concat($jobs)->values()->all();
    }

    private function validated(Request $request, ?ClearingOfficer $officer = null): array
    {
        $req = $officer ? 'sometimes' : 'required';
        $data = $request->validate([
            'name' => [$req, 'string', 'max:120'],
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:150',
            'company' => 'nullable|string|max:150',
            'licence_number' => 'nullable|string|max:60',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ], ['name.required' => 'Enter the clearing officer\'s name.']);

        return $officer ? array_filter($data, fn ($k) => $request->has($k), ARRAY_FILTER_USE_KEY) : $data;
    }
}
