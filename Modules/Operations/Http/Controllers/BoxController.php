<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Services\CanonicalInput;

/**
 * Boxes / lite cargo: small consignments owned by other clients that ride in a
 * car container (mostly from Canada). Loading, sailing, arrival, offloading
 * and clearing all follow the container, exactly like the cars inside it.
 */
class BoxController extends Controller
{
    public const VIEWS = [
        'open' => 'Not delivered',
        'not_loaded' => 'Not loaded yet',
        'in_transit' => 'Loaded, not offloaded',
        'unassigned' => 'Not in a container',
        'all' => 'All boxes',
    ];

    public function index(Request $request)
    {
        $query = Shipment::boxes()->with(['client:id,company_name,contact_person', 'container:id,container_number,status']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $compact = strtoupper(preg_replace('/\W/', '', $search));
            $query->where(function ($q) use ($search, $compact) {
                $q->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('cargo_description', 'like', "%{$search}%")
                    ->orWhere('consignee', 'like', "%{$search}%")
                    ->orWhereHas('container', fn ($c) => $c->where('container_number', 'like', "%{$compact}%"))
                    ->orWhereHas('client', fn ($c) => $c->where('company_name', 'like', "%{$search}%")->orWhere('contact_person', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('container_id')) {
            $query->where('container_id', $request->container_id);
        }

        match ($request->input('view', 'open')) {
            'open' => $query->where('status', '!=', Shipment::STATUS_DELIVERED),
            'not_loaded' => $query->whereIn('status', [Shipment::STATUS_ADDED, Shipment::STATUS_AT_YARD]),
            'in_transit' => $query->whereIn('status', [Shipment::STATUS_LOADED, Shipment::STATUS_SAILED, Shipment::STATUS_ARRIVED]),
            'unassigned' => $query->whereNull('container_id'),
            default => null,
        };

        return Inertia::render('Operations/Boxes/Index', [
            'boxes' => $query->orderByDesc('date_added')->orderByDesc('id')->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'view', 'container_id']),
            'statuses' => Shipment::BOX_STATUSES,
            'views' => self::VIEWS,
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function show(Request $request, Shipment $box)
    {
        abort_unless($box->isBox(), 404);

        $box->load(['client', 'container.shippingLine:id,name,tracking_url', 'milestones.user:id,name', 'charges.invoice:id,invoice_number,status']);
        $user = $request->user();

        $costs = (float) $box->charges->where('kind', 'cost')->sum('amount');
        $charges = (float) $box->charges->where('kind', 'charge')->sum('amount');

        return Inertia::render('Operations/Boxes/Show', [
            'box' => $box,
            'statuses' => Shipment::BOX_STATUSES,
            'trackingUrl' => $box->container?->shippingLine?->trackingUrlFor($box->container->container_number),
            'money' => $user?->can('money.view') ? [
                'total_cost' => round($costs, 2),
                'total_charges' => round($charges, 2),
                'profit' => round($charges - $costs, 2),
            ] : null,
            'chargeCategories' => ShipmentCharge::BOX_CATEGORIES,
            'documents' => DocumentController::panel($box, $user),
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function store(Request $request, CanonicalInput $canonical)
    {
        $data = $this->validated($request, $canonical);
        $containerId = $data['container_id'] ?? null;
        unset($data['container_id']);

        $box = Shipment::create($data + [
            'cargo_type' => Shipment::CARGO_BOX,
            'service_type' => 'Box in car container',
            'created_by' => $request->user()?->id,
        ]);
        $box->milestones()->create([
            'status' => $box->status,
            'occurred_on' => $box->date_added,
            'note' => 'Box booked',
            'user_id' => $request->user()?->id,
        ]);

        if ($containerId) {
            app(ContainerController::class)->assign(Container::findOrFail($containerId), collect([$box]));
        }

        return redirect()->route('operations.boxes.show', $box)->with('success', "Box {$box->tracking_id} added.");
    }

    public function update(Request $request, Shipment $box, CanonicalInput $canonical)
    {
        abort_unless($box->isBox(), 404);

        $data = $this->validated($request, $canonical, $box);
        $newStatus = $data['status'] ?? $box->status;
        $hasContainer = array_key_exists('container_id', $data);
        $containerId = $data['container_id'] ?? null;
        unset($data['status'], $data['container_id']);

        $box->update($data);
        if ($hasContainer && $containerId !== $box->container_id) {
            if ($containerId) {
                app(ContainerController::class)->assign(Container::findOrFail($containerId), collect([$box]));
            } else {
                $box->update(['container_id' => null, 'container_slot' => null]);
            }
        }
        if ($newStatus !== $box->fresh()->status) {
            $box->transitionTo($newStatus);
        }

        return redirect()->back()->with('success', 'Box updated.');
    }

    public function updateStatus(Request $request, Shipment $box)
    {
        abort_unless($box->isBox(), 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Shipment::BOX_STATUSES))],
            'date' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $box->transitionTo($validated['status'], $validated['date'] ?? null, $validated['note'] ?? null);

        return redirect()->back()->with('success', "Status set to {$box->status_label}.");
    }

    public function destroy(Shipment $box)
    {
        abort_unless($box->isBox(), 404);
        $box->delete();

        return redirect()->route('operations.boxes.index')->with('success', "Box {$box->tracking_id} deleted.");
    }

    private function validated(Request $request, CanonicalInput $canonical, ?Shipment $box = null): array
    {
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'consignee' => 'nullable|string|max:150',
            'cargo_description' => [$box ? 'sometimes' : 'required', 'string', 'max:255'],
            'package_count' => 'nullable|integer|min:1|max:10000',
            'package_type' => 'nullable|string|max:40',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'declared_value' => 'nullable|numeric|min:0',
            'declared_currency' => 'nullable|string|size:3',
            'container_id' => ['nullable', Rule::exists('containers', 'id')->whereNull('cargo_shipment_id')->whereNull('deleted_at')],
            'destination_city' => 'nullable|string|max:80',
            'date_added' => 'nullable|date',
            'status' => ['nullable', Rule::in(array_keys(Shipment::BOX_STATUSES))],
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ], [
            'cargo_description.required' => 'Say what is in the box, e.g. "Clothes and shoes" or "Auto parts".',
            'container_id.exists' => 'Pick a car container for the box.',
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
            unset($data['declared_value']);
        }
        if (!$user?->can('cargo.status')) {
            unset($data['status']);
        }

        if ($box) {
            return array_filter($data, fn ($v, $k) => $request->has($k === 'destination' ? 'destination_city' : $k), ARRAY_FILTER_USE_BOTH);
        }

        $data['destination_city'] ??= 'Lagos';
        $data['destination'] ??= $data['destination_city'];

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'packageTypes' => ['Box', 'Cartons', 'Bags', 'Barrel', 'Pallet', 'Suitcase', 'Pieces'],
            'clients' => Client::orderBy('company_name')->limit(500)->get(['id', 'company_name', 'contact_person']),
            'users' => User::select('id', 'name')->orderBy('name')->limit(100)->get(),
            'containers' => Container::forVehicles()->with('shippingLine:id,name')
                ->where('status', '!=', Container::STATUS_RELEASED)->orderByDesc('id')->limit(100)
                ->get(['id', 'container_number', 'shipping_line_id', 'status']),
        ];
    }
}
