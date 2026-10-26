<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->brandName('Mapleby')
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('favicon.ico'))
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile()
            ->colors([
                'primary' => Color::hex('#ED1722'),
                'gray' => [
                    50 => '250, 250, 248', 100 => '243, 243, 241', 200 => '221, 222, 223',
                    300 => '201, 202, 203', 400 => '154, 156, 160', 500 => '98, 101, 106',
                    600 => '75, 78, 82', 700 => '57, 59, 62', 800 => '36, 36, 36', 900 => '23, 24, 25', 950 => '17, 18, 19',
                ],
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Inter')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarFullyCollapsibleOnDesktop()
            ->breadcrumbs()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook(\Filament\View\PanelsRenderHook::HEAD_END, fn () => app(\Illuminate\Foundation\Vite::class)('resources/css/filament/admin/theme.css'))
            // The collapsible-sidebar store persists "open"; on phones start with it closed.
            ->renderHook(\Filament\View\PanelsRenderHook::BODY_END, fn () => new \Illuminate\Support\HtmlString(
                "<script>document.addEventListener('alpine:initialized', () => { if (window.innerWidth < 1024) Alpine.store('sidebar').close(); });</script>"
            ))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: base_path('Modules/CRM/Filament/Resources'), for: 'Modules\\CRM\\Filament\\Resources')
            ->discoverResources(in: base_path('Modules/Core/Filament/Resources'), for: 'Modules\\Core\\Filament\\Resources')
            // ->discoverResources(in: base_path('Modules/Operations/Filament/Resources'), for: 'Modules\\Operations\\Filament\\Resources') // Path does not exist
            ->discoverResources(in: base_path('Modules/Inventory/Filament/Resources'), for: 'Modules\\Inventory\\Filament\\Resources')
            ->discoverResources(in: base_path('Modules/Quoting/Filament/Resources'), for: 'Modules\\Quoting\\Filament\\Resources')
            ->discoverResources(in: base_path('Modules/Tasks/Filament/Resources'), for: 'Modules\\Tasks\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverPages(in: base_path('Modules/Core/Filament/Pages'), for: 'Modules\\Core\\Filament\\Pages')
            ->discoverPages(in: base_path('Modules/Inventory/Filament/Pages'), for: 'Modules\\Inventory\\Filament\\Pages')
            ->discoverPages(in: base_path('Modules/Reports/Filament/Pages'), for: 'Modules\\Reports\\Filament\\Pages')
            ->discoverPages(in: base_path('Modules/CRM/Filament/Pages'), for: 'Modules\\CRM\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->discoverWidgets(in: base_path('Modules/Tasks/Filament/Widgets'), for: 'Modules\\Tasks\\Filament\\Widgets')
            ->discoverWidgets(in: base_path('Modules/Reports/Filament/Widgets'), for: 'Modules\\Reports\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(\Filament\View\PanelsRenderHook::BODY_END, fn() => view('filament.hooks.fab'));
    }
}
