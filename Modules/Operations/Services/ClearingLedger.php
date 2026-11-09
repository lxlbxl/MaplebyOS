<?php

namespace Modules\Operations\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Operations\Entities\ClearingAssignment;
use Modules\Operations\Entities\ClearingOfficer;
use Modules\Operations\Entities\ClearingWork;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;

/**
 * Who clears what, and who gets the credit.
 *
 * A "clearing" is a car container, or a general cargo clearing job (which
 * clears all its containers together under one Bill of Lading). It has one
 * clearing officer at a time. Work is credited to the officer holding the
 * clearing when it happened and stays with them after a reassignment. Only
 * an override moves credit, and every assignment, reassignment and override
 * is written to the activity log ('clearing').
 */
class ClearingLedger
{
    public const LOG = 'clearing';

    /** The clearing a container, car, box or clearing job belongs to (null if none). */
    public static function clearableFor(?Model $model): ?Model
    {
        if ($model instanceof Container) {
            return $model->cargo_shipment_id ? $model->cargoShipment : $model;
        }
        if ($model instanceof Shipment) {
            if ($model->isGeneralCargo()) {
                return $model;
            }

            return $model->container_id ? $model->container : null;
        }

        return null;
    }

    public static function label(?Model $clearable): string
    {
        if ($clearable instanceof Container) {
            return "container {$clearable->container_number}";
        }
        if ($clearable instanceof Shipment) {
            return "clearing job {$clearable->tracking_id}";
        }

        return 'clearing';
    }

    public function current(Model $clearable): ?ClearingAssignment
    {
        return ClearingAssignment::where('clearable_type', $clearable->getMorphClass())
            ->where('clearable_id', $clearable->getKey())
            ->whereNull('ended_at')
            ->latest('started_at')->latest('id')
            ->first();
    }

    /**
     * Give the clearing to an officer. Any current officer's spell ends now;
     * their earlier work stays credited to them unless $moveEarlierWork is
     * set, which is an override and needs a reason.
     */
    public function assign(Model $clearable, ClearingOfficer $officer, ?User $by, ?string $reason = null, bool $moveEarlierWork = false, ?string $overrideReason = null): ClearingAssignment
    {
        return DB::transaction(function () use ($clearable, $officer, $by, $reason, $moveEarlierWork, $overrideReason) {
            $now = Carbon::now();
            $previous = $this->current($clearable);

            if ($previous && $previous->clearing_officer_id === $officer->id) {
                return $previous;
            }

            if ($previous) {
                $previous->update(['ended_at' => $now, 'ended_by' => $by?->id]);
            }

            $assignment = ClearingAssignment::create([
                'clearable_type' => $clearable->getMorphClass(),
                'clearable_id' => $clearable->getKey(),
                'clearing_officer_id' => $officer->id,
                'started_at' => $now,
                'assigned_by' => $by?->id,
                'reason' => $reason,
            ]);

            $previousOfficer = $previous?->officer;
            $where = self::label($clearable);
            activity(self::LOG)
                ->performedOn($clearable)
                ->causedBy($by)
                ->event($previous ? 'reassigned' : 'assigned')
                ->withProperties(array_filter([
                    'from_officer_id' => $previousOfficer?->id,
                    'from_officer' => $previousOfficer?->name,
                    'to_officer_id' => $officer->id,
                    'to_officer' => $officer->name,
                    'reason' => $reason,
                    'assignment_id' => $assignment->id,
                ], fn ($v) => $v !== null))
                ->log($previous
                    ? "Reassigned {$where} from {$previousOfficer?->name} to {$officer->name}"
                    : "Assigned {$where} to {$officer->name}");

            if ($moveEarlierWork && $previous) {
                $earlier = $this->workFor($clearable)->where('clearing_officer_id', '!=', $officer->id)->get();
                $this->override($earlier, $officer, $by, $overrideReason ?: 'Moved with reassignment', $clearable);
            }

            return $assignment;
        });
    }

    /**
     * Move credit for some work to another officer. Each row keeps who it was
     * originally credited to, and the move is logged with every row it touched.
     *
     * @param iterable<ClearingWork> $work
     */
    public function override(iterable $work, ClearingOfficer $to, ?User $by, string $reason, ?Model $clearable = null): int
    {
        $moved = [];
        foreach ($work as $row) {
            if ($row->clearing_officer_id === $to->id) {
                continue;
            }
            $moved[] = [
                'work_id' => $row->id,
                'description' => $row->description,
                'from_officer_id' => $row->clearing_officer_id,
                'from_officer' => $row->officer?->name,
            ];
            $row->update([
                'clearing_officer_id' => $to->id,
                'overridden_by' => $by?->id,
                'overridden_at' => Carbon::now(),
                'override_reason' => $reason,
            ]);
            $clearable ??= $row->clearable;
        }

        if ($moved) {
            $count = count($moved);
            $from = collect($moved)->pluck('from_officer')->filter()->unique()->implode(', ') ?: 'no officer';
            activity(self::LOG)
                ->performedOn($clearable)
                ->causedBy($by)
                ->event('credit_overridden')
                ->withProperties(['to_officer_id' => $to->id, 'to_officer' => $to->name, 'reason' => $reason, 'work' => $moved])
                ->log("Credit for {$count} " . ($count === 1 ? 'piece' : 'pieces') . " of work on " . self::label($clearable) . " moved from {$from} to {$to->name}");
        }

        return count($moved);
    }

    /**
     * Credit a piece of work to whoever holds the clearing now. Does nothing
     * when no officer is assigned yet.
     */
    public function record(?Model $clearable, string $action, string $description, ?Model $subject = null, ?User $by = null, $when = null): ?ClearingWork
    {
        if (!$clearable || !$clearable->getKey()) {
            return null;
        }
        $assignment = $this->current($clearable);
        if (!$assignment) {
            return null;
        }

        return ClearingWork::create([
            'clearable_type' => $clearable->getMorphClass(),
            'clearable_id' => $clearable->getKey(),
            'clearing_officer_id' => $assignment->clearing_officer_id,
            'original_officer_id' => $assignment->clearing_officer_id,
            'assignment_id' => $assignment->id,
            'action' => $action,
            'description' => mb_substr($description, 0, 500),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'user_id' => $by?->id ?? auth()->id(),
            'occurred_at' => $this->when($when),
        ]);
    }

    /** A bare date for today means "now", so same-day work keeps its order and time. */
    private function when($when): Carbon
    {
        if (!$when) {
            return Carbon::now();
        }
        $at = Carbon::parse($when);

        return ($at->isToday() && $at->format('H:i:s') === '00:00:00') ? Carbon::now() : $at;
    }

    public function workFor(Model $clearable)
    {
        return ClearingWork::where('clearable_type', $clearable->getMorphClass())->where('clearable_id', $clearable->getKey());
    }

    /**
     * Everything the clearing officer panel shows on a container or clearing job.
     */
    public function panel(Model $clearable, ?User $user): ?array
    {
        if (!$user?->can('clearing.view')) {
            return null;
        }

        $assignments = ClearingAssignment::where('clearable_type', $clearable->getMorphClass())
            ->where('clearable_id', $clearable->getKey())
            ->with(['officer:id,name,phone,company,deleted_at', 'assigner:id,name', 'ender:id,name'])
            ->orderByDesc('started_at')->orderByDesc('id')->get();

        return [
            'clearable' => [
                'type' => $clearable instanceof Container ? 'container' : 'job',
                'id' => $clearable->getKey(),
                'label' => self::label($clearable),
            ],
            'current' => $assignments->firstWhere('ended_at', null),
            'assignments' => $assignments,
            'work' => $this->workFor($clearable)
                ->with(['officer:id,name,deleted_at', 'originalOfficer:id,name,deleted_at', 'user:id,name', 'overrider:id,name'])
                ->orderByDesc('occurred_at')->orderByDesc('id')->limit(200)->get(),
            'audit' => self::audit($clearable, $user),
            'officers' => ClearingOfficer::active()->orderBy('name')->get(['id', 'name', 'company', 'phone']),
            'actions' => ClearingWork::ACTIONS,
        ];
    }

    /** The activity log for a clearing: assignments, reassignments, overrides and deposit changes. */
    public static function audit(Model $clearable, ?User $user, int $limit = 100): array
    {
        $depositIds = [];
        if ($user?->can('deposits.view')) {
            $containerIds = $clearable instanceof Container
                ? [$clearable->id]
                : ($clearable instanceof Shipment ? $clearable->cargoContainers()->pluck('id')->all() : []);
            $depositIds = \Modules\Operations\Entities\ContainerDeposit::withTrashed()->whereIn('container_id', $containerIds)->pluck('id')->all();
        }

        return \Spatie\Activitylog\Models\Activity::query()
            ->where(function ($q) use ($clearable, $depositIds) {
                $q->where(fn ($q) => $q->where('subject_type', $clearable->getMorphClass())->where('subject_id', $clearable->getKey()));
                if ($depositIds) {
                    $q->orWhere(fn ($q) => $q->where('subject_type', (new \Modules\Operations\Entities\ContainerDeposit)->getMorphClass())->whereIn('subject_id', $depositIds));
                }
            })
            ->whereIn('log_name', [self::LOG, DepositLedger::LOG])
            ->with('causer')
            ->latest('id')->limit($limit)->get()
            ->map(fn ($a) => self::auditRow($a))
            ->all();
    }

    public static function auditRow($activity): array
    {
        return [
            'id' => $activity->id,
            'log' => $activity->log_name,
            'event' => $activity->event,
            'description' => $activity->description,
            'by' => $activity->causer?->name,
            'at' => $activity->created_at?->toIso8601String(),
            'properties' => $activity->properties,
        ];
    }
}
