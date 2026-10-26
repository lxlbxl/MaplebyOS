<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        try {
            // Check if settings table exists to avoid migration errors
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $appName = \Modules\Core\Entities\Setting::get('app_name', config('app.name'));
                $favicon = \Modules\Core\Entities\Setting::get('site_favicon');
                $logo = \Modules\Core\Entities\Setting::get('site_logo');

                // Override config
                if ($appName) {
                    config(['app.name' => $appName]);
                }

                // Share with Blade views
                \Illuminate\Support\Facades\View::share('app_name', $appName);

                if ($favicon) {
                    \Illuminate\Support\Facades\View::share('favicon', \Illuminate\Support\Facades\Storage::url($favicon));
                }

                if ($logo) {
                    \Illuminate\Support\Facades\View::share('site_logo', \Illuminate\Support\Facades\Storage::url($logo));
                }
            }
        } catch (\Exception $e) {
            // Log or ignore db connection errors during boot
        }
    }
}
