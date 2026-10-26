<?php

namespace Modules\Operations\Providers;

use Illuminate\Support\ServiceProvider;

class OperationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerObservers();
    }

    protected function registerObservers(): void
    {
        // \Modules\Operations\Entities\Shipment::observe(\Modules\Operations\Observers\ShipmentObserver::class);
    }
}
