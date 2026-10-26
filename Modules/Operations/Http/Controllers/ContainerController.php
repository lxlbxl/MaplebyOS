<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Services\CanonicalInput;
use Modules\Operations\Services\ContainerNumber;

class ContainerController extends Controller
{
    public function index(Request $request)
    {
        $query = Container::with(['shippingLine:id,name,tracking_url', 'shipments:id,container_id,tracking_id,vehicle_year,vehicle_make,vehicle_model,vin,status,container_slot'])
            ->withCount('shipments');

        if ($request->filled('search')) {
            $search = strtoupper(preg_replace('/\W/', '', $request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->where('container_number', 'like', "%{$search}%")
                    ->orWhere('booking_number', 'like', "%{$search}%")
                    ->orWhere('bl_number', 'like', "%{$search}%")
                    ->orWhereHas('shipments', fn ($s) => $s->where('vin', 'like', "%{$search}%")->orWhere('tracking_id', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('shipping_line_id')) {
            $query->where('shipping_line_id', $request->shipping_line_id);
        }
        if (!$request->boolean('include_released')) {
            $query->where(function ($q) {
                $q->where('status', '!=', Container::STATUS_RELEASED)
                    ->orWhere('released_on', '>=', now()->subDays(30));
            });
        }

        $containers = $query->orderByRaw('COALESCE(loaded_on, created_at) DESC')->limit(300)->get()
            ->each(fn ($c) => $c->setAttribute('tracking_link', $c->shippingLine?->trackingUrlFor($c->container_number)));

        $completed = Container::with('shippingLine:id,name')->whereNotNull('offloaded_on')->whereNotNull('loaded_on')->get();
        $transitByLine = $completed->groupBy(fn ($c) => $c->shippingLine?->name ?? 'Unknown')
            ->map(fn ($group, $line) => [
                'line' => $line,
                'containers' => $group->count(),
                'avg_days' => (int) round($group->avg(fn ($c) => $c->transitDays())),
            ])->values();

        return Inertia::render('Operations/Containers/Index', [
            'containers' => $containers,
            'statuses' => Container::getStatuses(),
            'shippingLines' => ShippingLine::orderBy('name')->get(['id', 'name', 'container_prefixes']),
            'filters' => $request->only(['search', 'shipping_line_id', 'include_released']),
            'summary' => [
                'in_transit' => Container::whereIn('status', [Container::STATUS_LOADED, Container::STATUS_SAILED])->count(),
                'arrived' => Container::whereIn('status', [Container::STATUS_ARRIVED, Container::STATUS_OFFLOADED])->count(),
                'cars_in_transit' => Shipment::whereIn('status', [Shipment::STATUS_LOADED, Shipment::STATUS_SAILED, Shipment::STATUS_ARRIVED])->count(),
                'transit_by_line' => $transitByLine,
            ],
        ]);
    }

    public function show(Container $container)
    {
        $container->load(['shippingLine', 'shipments.client:id,company_name,contact_person', 'milestones.user:id,name']);

        return Inertia::render('Operations/Containers/Show', [
            'container' => $container,
            'statuses' => Container::getStatuses(),
            'shippingLines' => ShippingLine::orderBy('name')->get(['id', 'name', 'container_prefixes']),
            'freightShare' => $container->freightSharePerVehicle(),
            'transitDays' => $container->transitDays(),
            'trackingUrl' => $container->shippingLine?->trackingUrlFor($container->container_number),
            'unassigned' => Shipment::whereNull('container_id')
                ->whereNotIn('status', [Shipment::STATUS_DELIVERED, Shipment::STATUS_CLEARED])
                ->orderByDesc('date_added')->limit(200)
                ->get(['id', 'tracking_id', 'vin', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $open = Container::where('container_number', $data['container_number'])
            ->where('status', '!=', Container::STATUS_RELEASED)->first();
        if ($open) {
            throw ValidationException::withMessages([
                'container_number' => "Container {$open->container_number} is already open (status: {$open->status_label}). Open it instead of creating a duplicate.",
            ]);
        }

        $data['created_by'] = $request->user()?->id;
        $data['status'] ??= Container::STATUS_PLANNED;
        $container = Container::create($data);

        if ($request->filled('shipment_ids')) {
            $this->assign($container, Shipment::whereIn('id', $request->input('shipment_ids'))->get());
        }

        return redirect()->route('operations.containers.show', $container)->with('success', "Container {$container->container_number} created.");
    }

    public function update(Request $request, Container $container)
    {
        $data = $this->validated($request, $container);
        unset($data['status']);
        $container->update($data);

        return redirect()->back()->with('success', 'Container updated.');
    }

    public function destroy(Container $container)
    {
        $container->shipments()->update(['container_id' => null, 'container_slot' => null]);
        $container->delete();

        return redirect()->route('operations.containers.index')->with('success', 'Container deleted; its vehicles are unassigned.');
    }

    /**
     * Change the container's status and cascade it to every car inside.
     */
    public function updateStatus(Request $request, Container $container)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Container::getStatuses()))],
            'date' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $container->transitionTo($validated['status'], $validated['date'] ?? null, $validated['note'] ?? null);

        return redirect()->back()->with('success', "Container {$container->container_number} and its {$container->shipments()->count()} vehicles set to {$container->status_label}.");
    }

    public function addVehicles(Request $request, Container $container)
    {
        $validated = $request->validate([
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'integer|exists:shipments,id',
        ]);

        $this->assign($container, Shipment::whereIn('id', $validated['shipment_ids'])->get());

        return redirect()->back()->with('success', 'Vehicles added to the container.');
    }

    public function removeVehicle(Container $container, Shipment $shipment)
    {
        abort_unless($shipment->container_id === $container->id, 404);
        $shipment->update(['container_id' => null, 'container_slot' => null]);

        return redirect()->back()->with('success', "{$shipment->tracking_id} removed from the container.");
    }

    /**
     * Put vehicles into free slots. Refuses to overfill unless capacity is raised.
     */
    public function assign(Container $container, Collection $shipments): void
    {
        $shipments = $shipments->reject(fn ($s) => $s->container_id === $container->id);
        $free = $container->freeSlots();
        if ($shipments->count() > $free) {
            throw ValidationException::withMessages([
                'shipment_ids' => "Container {$container->container_number} has {$free} free slot(s) of {$container->capacity}. Remove a car or raise the capacity first.",
            ]);
        }

        $used = $container->shipments()->pluck('container_slot')->filter()->all();
        $slot = 1;
        foreach ($shipments as $shipment) {
            while (in_array($slot, $used, true)) {
                $slot++;
            }
            $shipment->update(['container_id' => $container->id, 'container_slot' => $slot]);
            $used[] = $slot;

            // Cars added to a container that has already moved catch up to its status.
            if (isset(Container::CASCADE[$container->status])) {
                $shipment->transitionTo(Container::CASCADE[$container->status][1], $container->{Container::CASCADE[$container->status][0]}, "Added to container {$container->container_number}", $container->id);
            }
        }
    }

    private function validated(Request $request, ?Container $container = null): array
    {
        $request->merge(['container_number' => ContainerNumber::clean($request->input('container_number'))]);

        $data = $request->validate([
            'container_number' => ['required', 'string', 'regex:/^[A-Z]{4}\d{7}$/'],
            'size_type' => 'nullable|in:20GP,40GP,40HC,45HC',
            'seal_number' => 'nullable|string|max:40',
            'shipping_line' => 'nullable|string|max:80',
            'booking_number' => 'nullable|string|max:60',
            'bl_number' => 'nullable|string|max:60',
            'vessel' => 'nullable|string|max:80',
            'voyage' => 'nullable|string|max:40',
            'port_of_loading' => 'nullable|string|max:80',
            'port_of_discharge' => 'nullable|string|max:80',
            'status' => ['nullable', Rule::in(array_keys(Container::getStatuses()))],
            'capacity' => 'nullable|integer|min:1|max:12',
            'loaded_on' => 'nullable|date',
            'etd' => 'nullable|date',
            'sailed_on' => 'nullable|date',
            'eta' => 'nullable|date',
            'arrived_on' => 'nullable|date',
            'offloaded_on' => 'nullable|date',
            'released_on' => 'nullable|date',
            'freight_cost' => 'nullable|numeric|min:0',
            'freight_currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ], [
            'container_number.regex' => 'A container number is 4 letters followed by 7 digits, e.g. MSNU7239445.',
        ]);

        $data = app(CanonicalInput::class)->apply($data, [
            'shipping_line' => ['shipping_lines', 'shipping line'],
            'port_of_loading' => ['ports', 'port of loading', ['kind' => 'origin']],
            'port_of_discharge' => ['ports', 'port of discharge', ['kind' => 'destination']],
        ]);

        $data['shipping_line_id'] = $data['shipping_line']
            ? ShippingLine::where('name', $data['shipping_line'])->value('id')
            : ShippingLine::guessFromContainerNumber($data['container_number'])?->id;
        unset($data['shipping_line']);
        $data['capacity'] ??= 4;

        if ($container) {
            return array_filter($data, fn ($v, $k) => $request->has($k === 'shipping_line_id' ? 'shipping_line' : $k), ARRAY_FILTER_USE_BOTH);
        }

        return $data;
    }
}
