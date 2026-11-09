<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Services\VinDecoder;

/**
 * Public "where is my shipment" lookup for cars, boxes and clearing jobs. Needs
 * the exact tracking ID, full VIN or (for clearing) Bill of Lading number and
 * only returns the item, status and shipping dates (no customer or money).
 */
class PublicTrackingController extends Controller
{
    public function lookup(Request $request)
    {
        $request->validate(['q' => 'required|string|min:6|max:40']);
        $q = strtoupper(trim($request->input('q')));
        $vin = VinDecoder::clean($q);

        $bl = preg_replace('/\s+/', '', $q);

        $shipment = Shipment::with(['container.shippingLine:id,name', 'cargoContainers.shippingLine:id,name', 'milestones'])
            ->where('tracking_id', $q)
            ->when(strlen($vin) === 17, fn ($query) => $query->orWhere('vin', $vin))
            ->orWhere(fn ($query) => $query->where('cargo_type', Shipment::CARGO_GENERAL)->where('bl_number', $bl))
            ->first();

        if (!$shipment) {
            return response()->json(['found' => false, 'message' => 'Nothing found. Check the tracking ID (e.g. MAP26-10-023, BOX26-11-004 or CLR26-11-002), the full 17-character VIN, or your Bill of Lading number.'], 404);
        }

        $statuses = Shipment::statusesFor($shipment->cargo_type);
        $container = $shipment->container ?? $shipment->cargoContainers->first();
        $pipeline = array_diff_key($statuses, array_flip([Shipment::STATUS_ON_HOLD, Shipment::STATUS_ISSUE]));

        return response()->json([
            'found' => true,
            'tracking_id' => $shipment->tracking_id,
            'cargo_type' => $shipment->cargo_type,
            'vehicle' => $shipment->vehicle_label,
            'containers' => $shipment->isGeneralCargo() ? $shipment->cargoContainers->pluck('container_number')->values() : null,
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
            'steps' => collect($pipeline)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'timeline' => $shipment->milestones->map(fn ($m) => [
                'status' => $m->status,
                'label' => $statuses[$m->status] ?? $m->status,
                'date' => $m->occurred_on?->toDateString(),
            ])->values(),
        ]);
    }
}
