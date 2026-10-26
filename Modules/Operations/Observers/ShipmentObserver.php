<?php

namespace Modules\Operations\Observers;

use Modules\Operations\Entities\Shipment;

class ShipmentObserver
{
    public function created(Shipment $shipment): void
    {
        // Logic for created shipment
    }

    public function updated(Shipment $shipment): void
    {
        // Logic for updated shipment
    }

    public function deleted(Shipment $shipment): void
    {
        // Logic for deleted shipment
    }
}
