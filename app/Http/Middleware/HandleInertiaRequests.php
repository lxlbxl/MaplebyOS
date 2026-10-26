<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        $settings = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // Fetch app_name directly to bypass any caching issues
                $appNameSetting = \Modules\Core\Entities\Setting::where('key', 'app_name')->first();
                $appName = $appNameSetting ? $appNameSetting->value : null;

                $settings = [
                    'app_name' => $appName,
                    'currency' => \Modules\Core\Entities\Setting::get('default_currency', 'USD'),
                    'logo' => \Modules\Core\Entities\Setting::get('site_logo') ? \Illuminate\Support\Facades\Storage::url(\Modules\Core\Entities\Setting::get('site_logo')) : null,
                    'favicon' => \Modules\Core\Entities\Setting::get('site_favicon') ? \Illuminate\Support\Facades\Storage::url(\Modules\Core\Entities\Setting::get('site_favicon')) : null,
                ];
            }
        } catch (\Exception $e) {
            // Fallback defaults
            $settings = ['currency' => 'USD'];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'settings' => $settings,
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'importReport' => fn() => $request->session()->get('importReport'),
            ],
        ]);
    }
}
