<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Services\CanonicalInput;
use Modules\Operations\Services\ClearingLedger;
use Modules\Operations\Services\ContainerNumber;

/**
 * General cargo clearing jobs: one Bill of Lading of a client's goods arriving
 * in Nigeria, in one or more containers, cleared through customs by Mapleby.
 */
class ClearingJobController extends Controller
{
    /** Nigerian terminals offered as suggestions (free text is still allowed). */
    public const TERMINALS = [
        'APM Terminals Apapa', 'ENL Consortium Apapa', 'Apapa Bulk Terminal', 'Tin Can Island (TICT)',
        'Port & Cargo Terminal Tin Can', 'Five Star Logistics Tin Can', 'Lekki Deep Sea Port', 'Onne Port',
        'Port Harcourt', 'Off-dock terminal',
    ];

    /** Customs paperwork whose recording counts as clearing work. */
    public const PAPERWORK_FIELDS = [
        'form_m_number' => 'Form M',
        'paar_number' => 'PAAR',
        'customs_entry_number' => 'SGD',
        'duty_assessed' => 'duty assessment',
        'terminal' => 'terminal',
    ];

    public const VIEWS = [
        'open' => 'Open jobs',
        'awaiting_arrival' => 'Not arrived yet',
        'at_port' => 'At port, not released',
        'released' => 'Released, not delivered',
        'empty_due' => 'Delivered, empty not returned',
        'all' => 'All jobs',
    ];

    public function index(Request $request)
    {
        $query = Shipment::generalCargo()
            ->with(['client:id,company_name,contact_person', 'cargoContainers:id,cargo_shipment_id,container_number,size_type,status'])
            ->withCount('cargoContainers');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $compact = strtoupper(preg_replace('/\W/', '', $search));
            $query->where(function ($q) use ($search, $compact) {
                $q->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('bl_number', 'like', "%{$compact}%")
                    ->orWhere('form_m_number', 'like', "%{$search}%")
                    ->orWhere('paar_number', 'like', "%{$search}%")
                    ->orWhere('customs_entry_number', 'like', "%{$search}%")
                    ->orWhere('cargo_description', 'like', "%{$search}%")
                    ->orWhere('consignee', 'like', "%{$search}%")
                    ->orWhereHas('cargoContainers', fn ($c) => $c->where('container_number', 'like', "%{$compact}%"))
                    ->orWhereHas('client', fn ($c) => $c->where('company_name', 'like', "%{$search}%")->orWhere('contact_person', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $closed = [Shipment::STATUS_EMPTY_RETURNED];
        match ($request->input('view', 'open')) {
            'open' => $query->whereNotIn('status', $closed),
            'awaiting_arrival' => $query->whereIn('status', [Shipment::STATUS_AWAITING_DOCS, Shipment::STATUS_PRE_ARRIVAL]),
            'at_port' => $query->whereIn('status', [Shipment::STATUS_ARRIVED, Shipment::STATUS_DUTY_ASSESSED, Shipment::STATUS_DUTY_PAID, Shipment::STATUS_EXAMINATION]),
            'released' => $query->where('status', Shipment::STATUS_RELEASED),
            'empty_due' => $query->where('status', Shipment::STATUS_DELIVERED),
            default => null,
        };

        $jobs = $query->orderByRaw('COALESCE(eta, date_added) DESC')->orderByDesc('id')
            ->paginate(50)->withQueryString();

        $counts = Shipment::generalCargo()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('Operations/Clearing/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'status', 'view']),
            'statuses' => Shipment::CLEARING_STATUSES,
            'views' => self::VIEWS,
            'counts' => $counts,
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function show(Request $request, Shipment $job)
    {
        abort_unless($job->isGeneralCargo(), 404);

        $job->load([
            'client', 'assignee:id,name', 'milestones.user:id,name',
            'cargoContainers.shippingLine:id,name,tracking_url',
            'charges.invoice:id,invoice_number,status',
        ]);
        $job->cargoContainers->each(fn ($c) => $c->setAttribute('tracking_link', $c->shippingLine?->trackingUrlFor($c->container_number)));

        $user = $request->user();
        $seesMoney = (bool) $user?->can('money.view');
        $byCurrency = $job->charges->groupBy(fn ($c) => strtoupper($c->currency ?: 'NGN'))
            ->map(fn ($lines) => [
                'cost' => round((float) $lines->where('kind', 'cost')->sum('amount'), 2),
                'charge' => round((float) $lines->where('kind', 'charge')->sum('amount'), 2),
            ])
            ->map(fn ($t) => $t + ['profit' => round($t['charge'] - $t['cost'], 2)]);

        return Inertia::render('Operations/Clearing/Show', [
            'job' => $job,
            'statuses' => Shipment::CLEARING_STATUSES,
            'money' => $seesMoney ? $byCurrency : null,
            'chargeCategories' => ShipmentCharge::CLEARING_CATEGORIES,
            'documents' => DocumentController::panel($job, $user),
            'clearing' => app(ClearingLedger::class)->panel($job, $user),
            'deposits' => DepositController::panelFor($job->cargoContainers->pluck('id')->all(), $user),
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function store(Request $request, CanonicalInput $canonical)
    {
        $data = $this->validated($request, $canonical);
        $containers = $this->containerInput($request);

        $job = DB::transaction(function () use ($data, $containers, $request) {
            $job = Shipment::create($data + [
                'cargo_type' => Shipment::CARGO_GENERAL,
                'service_type' => 'Customs clearing',
                'created_by' => $request->user()?->id,
            ]);
            $job->milestones()->create([
                'status' => $job->status,
                'occurred_on' => $job->date_added,
                'note' => 'Clearing job opened',
                'user_id' => $request->user()?->id,
            ]);
            foreach ($containers as $container) {
                $this->attachContainer($job, $container);
            }

            return $job;
        });

        return redirect()->route('operations.clearing.show', $job)->with('success', "Clearing job {$job->tracking_id} opened.");
    }

    public function update(Request $request, Shipment $job, CanonicalInput $canonical)
    {
        abort_unless($job->isGeneralCargo(), 404);

        $data = $this->validated($request, $canonical, $job);
        $newStatus = $data['status'] ?? $job->status;
        unset($data['status']);

        $job->update($data);
        $paperwork = collect(self::PAPERWORK_FIELDS)->filter(fn ($label, $field) => $job->wasChanged($field) && filled($job->{$field}));
        if ($paperwork->isNotEmpty()) {
            app(ClearingLedger::class)->record($job, 'paperwork', 'Recorded ' . $paperwork->map(fn ($label, $field) => $field === 'duty_assessed' ? $label : "{$label} {$job->{$field}}")->implode(', '), null, $request->user());
        }
        if ($newStatus !== $job->status) {
            $job->transitionTo($newStatus);
        }

        return redirect()->back()->with('success', 'Clearing job updated.');
    }

    public function updateStatus(Request $request, Shipment $job)
    {
        abort_unless($job->isGeneralCargo(), 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Shipment::CLEARING_STATUSES))],
            'date' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $job->transitionTo($validated['status'], $validated['date'] ?? null, $validated['note'] ?? null);

        return redirect()->back()->with('success', "Status set to {$job->status_label}.");
    }

    public function destroy(Shipment $job)
    {
        abort_unless($job->isGeneralCargo(), 404);

        DB::transaction(function () use ($job) {
            $job->cargoContainers()->each(fn (Container $c) => $c->delete());
            $job->delete();
        });

        return redirect()->route('operations.clearing.index')->with('success', "Clearing job {$job->tracking_id} deleted.");
    }

    public function addContainer(Request $request, Shipment $job)
    {
        abort_unless($job->isGeneralCargo(), 404);

        $containers = $this->containerInput($request, required: true);
        foreach ($containers as $container) {
            $this->attachContainer($job, $container);
        }

        return redirect()->back()->with('success', count($containers) === 1 ? 'Container added.' : count($containers) . ' containers added.');
    }

    public function removeContainer(Shipment $job, Container $container)
    {
        abort_unless($job->isGeneralCargo() && $container->cargo_shipment_id === $job->id, 404);
        $container->delete();

        return redirect()->back()->with('success', "Container {$container->container_number} removed from the job.");
    }

    /**
     * Containers typed on the job form: one per line or comma separated,
     * optionally with a size after a space or colon ("MSCU1234565 40HC").
     *
     * @return array<int, array{container_number: string, size_type: ?string}>
     */
    private function containerInput(Request $request, bool $required = false): array
    {
        $request->validate([
            'containers' => [$required ? 'required' : 'nullable', 'string', 'max:2000'],
            'size_type' => 'nullable|in:20GP,40GP,40HC,45HC',
        ]);

        $rows = [];
        $errors = [];
        foreach (preg_split('/[\n,;]+/', (string) $request->input('containers')) as $line) {
            $line = strtoupper(trim($line));
            if ($line === '') {
                continue;
            }
            $size = null;
            if (preg_match('/\b(20GP|40GP|40HC|45HC|20|40)\b/', $line, $m)) {
                $size = match ($m[1]) { '20' => '20GP', '40' => '40GP', default => $m[1] };
                $line = trim(str_replace($m[0], '', $line));
            }
            $number = ContainerNumber::clean($line);
            if ($number === null && $size !== null && $rows) {
                // "MSCU1234565, 40HC": a size on its own belongs to the container before it.
                $rows[array_key_last($rows)]['size_type'] = $size;
                continue;
            }
            if (!preg_match('/^[A-Z]{4}\d{7}$/', (string) $number)) {
                $errors[] = "\"{$line}\" is not a container number (4 letters and 7 digits, e.g. MSCU1234565).";
                continue;
            }
            $rows[$number] = ['container_number' => $number, 'size_type' => $size ?? $request->input('size_type')];
        }

        if ($errors) {
            throw ValidationException::withMessages(['containers' => implode(' ', $errors)]);
        }
        if ($required && !$rows) {
            throw ValidationException::withMessages(['containers' => 'Enter at least one container number.']);
        }

        return array_values($rows);
    }

    private function attachContainer(Shipment $job, array $input): Container
    {
        $open = Container::where('container_number', $input['container_number'])
            ->where('status', '!=', Container::STATUS_RELEASED)->first();
        if ($open && $open->cargo_shipment_id !== $job->id) {
            $where = $open->cargo_shipment_id
                ? 'is already on clearing job ' . ($open->cargoShipment?->tracking_id ?? '#' . $open->cargo_shipment_id)
                : 'is already open as a car container';
            throw ValidationException::withMessages(['containers' => "Container {$open->container_number} {$where}."]);
        }
        if ($open) {
            return $open;
        }

        $statusFromJob = [
            Shipment::STATUS_ARRIVED => Container::STATUS_ARRIVED,
            Shipment::STATUS_DUTY_ASSESSED => Container::STATUS_ARRIVED,
            Shipment::STATUS_DUTY_PAID => Container::STATUS_ARRIVED,
            Shipment::STATUS_EXAMINATION => Container::STATUS_ARRIVED,
            Shipment::STATUS_RELEASED => Container::STATUS_RELEASED,
            Shipment::STATUS_DELIVERED => Container::STATUS_RELEASED,
            Shipment::STATUS_EMPTY_RETURNED => Container::STATUS_RELEASED,
        ][$job->status] ?? Container::STATUS_SAILED;

        return Container::create([
            'cargo_shipment_id' => $job->id,
            'container_number' => $input['container_number'],
            'size_type' => $input['size_type'],
            'bl_number' => $job->bl_number,
            'port_of_discharge' => $job->destination_city ?: 'Lagos',
            'eta' => $job->eta,
            'arrived_on' => $job->arrived_on,
            'released_on' => $job->released_on,
            'empty_returned_on' => $job->empty_returned_on,
            'status' => $statusFromJob,
            'capacity' => 1,
            'created_by' => $job->created_by,
        ]);
    }

    private function validated(Request $request, CanonicalInput $canonical, ?Shipment $job = null): array
    {
        if ($request->filled('bl_number')) {
            $request->merge(['bl_number' => strtoupper(preg_replace('/\s+/', '', $request->input('bl_number')))]);
        }

        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'consignee' => 'nullable|string|max:150',
            'cargo_description' => [$job ? 'sometimes' : 'required', 'string', 'max:255'],
            'hs_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.\s]+$/'],
            'package_count' => 'nullable|integer|min:1|max:1000000',
            'package_type' => 'nullable|string|max:40',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'declared_value' => 'nullable|numeric|min:0',
            'declared_currency' => 'nullable|string|size:3',
            'bl_number' => [
                'nullable', 'string', 'max:40',
                Rule::unique('shipments', 'bl_number')->ignore($job?->id)->whereNull('deleted_at')->where('cargo_type', Shipment::CARGO_GENERAL),
            ],
            'form_m_number' => 'nullable|string|max:40',
            'paar_number' => 'nullable|string|max:40',
            'customs_entry_number' => 'nullable|string|max:40',
            'terminal' => 'nullable|string|max:80',
            'destination_city' => 'nullable|string|max:80',
            'duty_assessed' => 'nullable|numeric|min:0',
            'eta' => 'nullable|date',
            'date_added' => 'nullable|date',
            'status' => ['nullable', Rule::in(array_keys(Shipment::CLEARING_STATUSES))],
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ], [
            'bl_number.unique' => 'Another clearing job already has this Bill of Lading number.',
            'hs_code.regex' => 'An HS code is digits and dots only, e.g. 8516.60.00.',
            'cargo_description.required' => 'Say what the cargo is, e.g. "Spare parts" or "Ceramic tiles".',
        ]);

        if (array_key_exists('destination_city', $data)) {
            $data = $canonical->apply($data, ['destination_city' => ['ports', 'destination']]);
            $data['destination'] = $data['destination_city'];
        }
        if (isset($data['declared_currency'])) {
            $data['declared_currency'] = strtoupper($data['declared_currency']);
        }

        $user = $request->user();
        if (!$user?->can('money.view')) {
            unset($data['duty_assessed'], $data['declared_value']);
        }
        if (!$user?->can('cargo.status')) {
            unset($data['status']);
        }

        if ($job) {
            return array_filter($data, fn ($v, $k) => $request->has($k === 'destination' ? 'destination_city' : $k), ARRAY_FILTER_USE_BOTH);
        }

        $data['destination_city'] ??= 'Lagos';
        $data['destination'] ??= $data['destination_city'];

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'terminals' => self::TERMINALS,
            'packageTypes' => ['Cartons', 'Pallets', 'Bags', 'Bales', 'Crates', 'Drums', 'Rolls', 'Pieces', 'Bundles', 'Loose'],
            'clients' => Client::orderBy('company_name')->limit(500)->get(['id', 'company_name', 'contact_person']),
            'users' => User::select('id', 'name')->orderBy('name')->limit(100)->get(),
            'shippingLines' => ShippingLine::orderBy('name')->get(['id', 'name']),
        ];
    }
}
