<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Entities\Setting;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settingsData' => [
                'general' => Setting::getGroup('general'),
                'email' => Setting::getGroup('email'),
                'finance' => Setting::getGroup('finance'),
                'security' => Setting::getGroup('security'),
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Update the settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'site_logo' => 'nullable|image|max:2048', // 2MB Max
            'site_favicon' => 'nullable|image|max:1024|dimensions:ratio=1/1', // Square image
        ]);

        // Handle File Uploads
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('uploads', 'public');
            Setting::set('site_logo', $path, 'string', 'general');
        }

        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('uploads', 'public');
            Setting::set('site_favicon', $path, 'string', 'general');
        }

        // Handle other settings
        if (isset($data['settings'])) {
            foreach ($data['settings'] as $key => $value) {
                // Skip file inputs in the settings array if they snuck in
                if ($key === 'site_logo' || $key === 'site_favicon')
                    continue;
                Setting::set($key, $value);
            }
        }

        // Explicitly clear critical cache keys to ensure immediate updates
        \Illuminate\Support\Facades\Cache::forget('setting.app_name');
        \Illuminate\Support\Facades\Cache::forget('setting.site_logo');
        \Illuminate\Support\Facades\Cache::forget('setting.site_favicon');

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Test SMTP configuration.
     */
    public function testSmtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'host' => 'required',
            'port' => 'required|integer',
            'encryption' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        try {
            $config = [
                'transport' => 'smtp',
                'host' => $request->host,
                'port' => $request->port,
                'encryption' => $request->encryption,
                'username' => $request->username,
                'password' => $request->password,
                'timeout' => null,
            ];

            $mailer = new \Illuminate\Mail\Mailer(
                'smtp',
                app('view'),
                new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory,
                new \Illuminate\Events\Dispatcher
            );

            // Dynamically configure the mailer is tricky in Laravel, easier to use raw SwiftMailer/Symfony Mailer
            // or Config::set but that affects global state.
            // Let's try setting config dynamically for this request.

            config(['mail.mailers.smtp_test' => $config]);

            // Use the created configuration to send a test email
            \Illuminate\Support\Facades\Mail::mailer('smtp_test')->raw('This is a test email from your application settings.', function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('SMTP Configuration Test');
            });

            return response()->json(['success' => true, 'message' => 'Connection successful! Test email sent to ' . $request->email]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()], 422);
        }
    }
}
