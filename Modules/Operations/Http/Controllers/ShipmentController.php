<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Services\CanonicalInput;
use Modules\Operations\Services\TrackingSheetImporter;
use Modules\Operations\Services\VinDecoder;

/**
 * Vehicles (one shipment row = one car).
 */
class ShipmentController extends Controller
{
    /** Saved views that mirror the questions people ask of the sheet. */
    public const VIEWS = [
        'all' => 'All vehicles',
        'in_transit' => 'Loaded, not offloaded',
        'not_loaded' => 'Not loaded yet',
        'no_vin' => 'Missing VIN',
        'no_customer' => 'No customer',
        'delivered_no_offload' => 'Delivered without offload date',
        'unassigned' => 'Not in a container',
    ];

    public function index(Request $request)
    {
        $query = Shipment::with(['client:id,company_name,contact_person', 'container.shippingLine:id,name']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('shipment_number', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', '%' . VinDecoder::clean($search) . '%')
                    ->orWhere('vehicle_make', 'like', "%{$search}%")
                    ->orWhere('vehicle_model', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('container', fn ($c) => $c->where('container_number', 'like', '%' . strtoupper(preg_replace('/\W/', '', $search)) . '%'))
                    ->orWhereHas('client', function ($c) use ($search) {
                        $c->where('company_name', 'like', "%{$search}%")->orWhere('contact_person', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('shipping_line_id')) {
            $query->whereHas('container', fn ($c) => $c->where('shipping_line_id', $request->shipping_line_id));
        }
        if ($request->filled('container_id')) {
            $query->where('container_id', $request->container_id);
        }
        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('Y-m', $request->month);
            $query->whereBetween('date_added', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
        }

        match ($request->input('view', 'all')) {
            'in_transit' => $query->whereIn('status', [Shipment::STATUS_LOADED, Shipment::STATUS_SAILED, Shipment::STATUS_ARRIVED]),
            'not_loaded' => $query->whereIn('status', [Shipment::STATUS_ADDED, Shipment::STATUS_PURCHASED, Shipment::STATUS_PICKED_UP, Shipment::STATUS_AT_YARD]),
            'no_vin' => $query->where(fn ($q) => $q->whereNull('vin')->orWhere('vin', '')),
            'no_customer' => $query->whereNull('client_id'),
            'delivered_no_offload' => $query->where('status', Shipment::STATUS_DELIVERED)->whereNull('offloaded_on'),
            'unassigned' => $query->whereNull('container_id'),
            default => null,
        };

        $sort = in_array($request->input('sort'), ['tracking_id', 'date_added', 'loaded_on', 'offloaded_on', 'vehicle_make', 'status'], true)
            ? $request->input('sort') : 'date_added';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $shipments = $query->orderBy($sort, $direction)->orderByDesc('id')
            ->paginate((int) min(200, max(10, (int) $request->input('per_page', 50))))
            ->withQueryString();

        $stats = Shipment::selectRaw('status, count(*) as value')->groupBy('status')->get()
            ->map(fn ($item) => ['name' => Shipment::getStatuses()[$item->status] ?? $item->status, 'value' => $item->value]);

        return Inertia::render('Operations/Shipments/Index', [
            'shipments' => $shipments,
            'filters' => $request->only(['search', 'status', 'view', 'shipping_line_id', 'container_id', 'month', 'sort', 'direction']),
            'statuses' => Shipment::getStatuses(),
            'views' => self::VIEWS,
            'shippingLines' => ShippingLine::orderBy('name')->get(['id', 'name']),
            'openContainers' => Container::with('shippingLine:id,name')->withCount('shipments')
                ->where('status', '!=', Container::STATUS_RELEASED)->orderByDesc('id')->limit(100)
                ->get(['id', 'container_number', 'shipping_line_id', 'capacity', 'status']),
            'users' => User::select('id', 'name')->limit(100)->get(),
            'stats' => $stats,
        ]);
    }

    public function show(Shipment $shipment)
    {
        $shipment->load([
            'client', 'assignee', 'quotes.payments', 'container.shippingLine', 'milestones.user:id,name',
            'charges.invoice:id,invoice_number,status',
        ]);

        $totalPaid = $shipment->quotes->sum(fn ($quote) => $quote->payments->sum('amount'));
        $totalQuoted = $shipment->quotes->sum('total_amount');

        $freightShare = $shipment->container?->freightSharePerVehicle();
        $costs = (float) $shipment->charges->where('kind', 'cost')->sum('amount') + (float) ($shipment->purchase_price ?? 0) + (float) ($freightShare ?? 0);
        $charges = (float) $shipment->charges->where('kind', 'charge')->sum('amount');

        return Inertia::render('Operations/Shipments/Show', [
            'shipment' => $shipment,
            'statuses' => Shipment::getStatuses(),
            'users' => User::select('id', 'name')->limit(100)->get(),
            'totalPaid' => $totalPaid,
            'totalQuoted' => $totalQuoted,
            'vin' => $shipment->vin ? VinDecoder::decode($shipment->vin) : null,
            'trackingUrl' => $shipment->container?->shippingLine?->trackingUrlFor($shipment->container->container_number),
            'money' => [
                'freight_share' => $freightShare,
                'total_cost' => round($costs, 2),
                'total_charges' => round($charges, 2),
                'profit' => round($charges - $costs, 2),
            ],
            'chargeCategories' => \Modules\Finance\Entities\ShipmentCharge::CATEGORIES,
        ]);
    }

    public function store(Request $request, CanonicalInput $canonical)
    {
        $data = $this->validated($request, $canonical);
        $data['service_type'] ??= 'Container shipping';

        $shipment = Shipment::create($data);
        $shipment->milestones()->create([
            'status' => $shipment->status,
            'occurred_on' => $shipment->date_added,
            'note' => 'Vehicle added',
            'user_id' => $request->user()?->id,
        ]);

        return redirect()->back()->with('success', "Vehicle {$shipment->tracking_id} added.");
    }

    public function update(Request $request, Shipment $shipment, CanonicalInput $canonical)
    {
        $data = $this->validated($request, $canonical, $shipment);
        $newStatus = $data['status'] ?? $shipment->status;
        unset($data['status']);

        $shipment->update($data);
        if ($newStatus !== $shipment->status) {
            $shipment->transitionTo($newStatus);
        }

        return redirect()->back()->with('success', 'Vehicle updated.');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();

        return redirect()->back()->with('success', 'Vehicle deleted.');
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Shipment::getStatuses()))],
            'date' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $shipment->transitionTo($validated['status'], $validated['date'] ?? null, $validated['note'] ?? null);

        return redirect()->back()->with('success', "Status set to {$shipment->status_label}.");
    }

    /**
     * Apply one change to many selected vehicles: status, or container assignment.
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:shipments,id',
            'action' => 'required|in:status,container,unassign',
            'status' => ['required_if:action,status', 'nullable', Rule::in(array_keys(Shipment::getStatuses()))],
            'date' => 'nullable|date',
            'container_id' => 'required_if:action,container|nullable|exists:containers,id',
        ]);

        $shipments = Shipment::whereIn('id', $validated['ids'])->get();

        if ($validated['action'] === 'status') {
            foreach ($shipments as $shipment) {
                $shipment->transitionTo($validated['status'], $validated['date'] ?? null);
            }

            return redirect()->back()->with('success', count($shipments) . ' vehicles updated.');
        }

        if ($validated['action'] === 'unassign') {
            Shipment::whereIn('id', $validated['ids'])->update(['container_id' => null, 'container_slot' => null]);

            return redirect()->back()->with('success', count($shipments) . ' vehicles removed from their container.');
        }

        $container = Container::findOrFail($validated['container_id']);
        app(ContainerController::class)->assign($container, $shipments);

        return redirect()->back()->with('success', count($shipments) . " vehicles added to {$container->container_number}.");
    }

    /**
     * Upload the tracking sheet CSV. Dry run first, then confirm.
     */
    public function import(Request $request, TrackingSheetImporter $importer)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'confirm' => 'nullable|boolean',
        ]);

        try {
            $report = $importer->import($request->file('file')->getRealPath(), !$request->boolean('confirm'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->back()->with('importReport', $report);
    }

    private function validated(Request $request, CanonicalInput $canonical, ?Shipment $shipment = null): array
    {
        $request->merge([
            'vin' => $request->filled('vin') ? VinDecoder::clean($request->input('vin')) : null,
        ]);

        $data = $request->validate([
            'vin' => [
                'nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/',
                Rule::unique('shipments', 'vin')->ignore($shipment?->id)->whereNull('deleted_at'),
            ],
            'client_id' => 'nullable|exists:clients,id',
            'status' => ['nullable', Rule::in(array_keys(Shipment::getStatuses()))],
            'vehicle_year' => 'nullable|integer|min:1950|max:' . ((int) date('Y') + 1),
            'vehicle_make' => 'nullable|string|max:60',
            'vehicle_model' => 'nullable|string|max:80',
            'vehicle_trim' => 'nullable|string|max:80',
            'vehicle_color' => 'nullable|string|max:40',
            'has_keys' => 'nullable|boolean',
            'date_added' => 'nullable|date',
            'destination_city' => 'nullable|string|max:80',
            'purchase_platform' => 'nullable|string|max:80',
            'lot_number' => 'nullable|string|max:60',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ], [
            'vin.size' => 'A VIN has exactly 17 characters.',
            'vin.regex' => 'A VIN uses only letters and digits, never I, O or Q.',
            'vin.unique' => 'This VIN is already on another vehicle.',
        ]);

        $data = $canonical->apply($data, [
            'vehicle_make' => ['makes', 'make'],
            'purchase_platform' => ['platforms', 'purchase platform'],
            'destination_city' => ['ports', 'destination'],
        ]);
        if (!empty($data['vehicle_model'])) {
            $data = $canonical->apply($data, [
                'vehicle_model' => ['models', 'model', $data['vehicle_make'] ? ['make' => $data['vehicle_make']] : []],
            ]);
        }

        $data['destination'] = $data['destination_city'] ?? null;

        if (!$shipment && empty($data['status'])) {
            $data['status'] = Shipment::STATUS_ADDED;
        }

        return array_filter($data, fn ($v, $k) => $shipment === null || $request->has($k === 'destination' ? 'destination_city' : $k), ARRAY_FILTER_USE_BOTH);
    }
}
