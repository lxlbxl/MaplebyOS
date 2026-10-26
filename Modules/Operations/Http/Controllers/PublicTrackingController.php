<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Services\VinDecoder;

/**
 * Public "where is my car" lookup. Needs the exact tracking ID or full VIN and
 * only returns vehicle, status and shipping dates (no customer or money).
 */
class PublicTrackingController extends Controller
{
    public function lookup(Request $request)
    {
        $request->validate(['q' => 'required|string|min:6|max:40']);
        $q = strtoupper(trim($request->input('q')));
        $vin = VinDecoder::clean($q);

        $shipment = Shipment::with(['container.shippingLine:id,name', 'milestones'])
            ->where('tracking_id', $q)
            ->when(strlen($vin) === 17, fn ($query) => $query->orWhere('vin', $vin))
            ->first();

        if (!$shipment) {
            return response()->json(['found' => false, 'message' => 'No vehicle found. Check the tracking ID (e.g. MAP26-10-023) or the full 17-character VIN.'], 404);
        }

        $statuses = Shipment::getStatuses();
        $container = $shipment->container;

        return response()->json([
            'found' => true,
            'tracking_id' => $shipment->tracking_id,
            'vehicle' => $shipment->vehicle_label,
            'vin_last6' => $shipment->vin ? substr($shipment->vin, -6) : null,
            'status' => $shipment->status,
            'status_label' => $shipment->status_label,
            'destination' => $shipment->destination_city,
            'container' => $container ? [
                'number' => $container->container_number,
                'shipping_line' => $container->shippingLine?->name,
                'vessel' => $container->vessel,
                'loaded_on' => $container->loaded_on?->toDateString(),
                'sailed_on' => $container->sailed_on?->toDateString(),
                'eta' => $container->eta?->toDateString(),
                'arrived_on' => $container->arrived_on?->toDateString(),
                'offloaded_on' => $container->offloaded_on?->toDateString(),
            ] : null,
            'steps' => collect(array_slice($statuses, 0, 10, true))->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'timeline' => $shipment->milestones->map(fn ($m) => [
                'status' => $m->status,
                'label' => $statuses[$m->status] ?? $m->status,
                'date' => $m->occurred_on?->toDateString(),
            ])->values(),
        ]);
    }
}
