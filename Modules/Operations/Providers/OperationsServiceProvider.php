<?php

namespace Modules\Operations\Providers;

use Illuminate\Support\ServiceProvider;

class OperationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerObservers();

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Operations\Console\ImportTrackingSheet::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->singleton(\Modules\Operations\Services\TrackingIdGenerator::class);
    }

    protected function registerObservers(): void
    {
        // \Modules\Operations\Entities\Shipment::observe(\Modules\Operations\Observers\ShipmentObserver::class);
    }
}
