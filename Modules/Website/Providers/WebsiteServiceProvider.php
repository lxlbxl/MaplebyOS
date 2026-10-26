<?php

namespace Modules\Website\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WebsiteServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Setup config if needed
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'website');
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        // Web routes for public website (no prefix, public access)
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');

        // API routes if needed
        Route::prefix('api/website')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
