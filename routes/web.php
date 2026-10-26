<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use Modules\CRM\Http\Controllers\ClientController;
use App\Http\Controllers\WebsiteController;

// Website Routes
Route::get('/', [WebsiteController::class, 'index'])->name('website.index');
Route::get('/about', [WebsiteController::class, 'about'])->name('website.about');
Route::get('/services', [WebsiteController::class, 'services'])->name('website.services');
Route::get('/inventory', [WebsiteController::class, 'inventory'])->name('website.inventory');
Route::get('/process', [WebsiteController::class, 'process'])->name('website.process');
Route::get('/blog', [WebsiteController::class, 'blog'])->name('website.blog');
Route::get('/contact', [WebsiteController::class, 'contact'])->name('website.contact');
Route::get('/shipment-request', [WebsiteController::class, 'shipmentRequest'])->name('website.shipment-request');
Route::get('/blog/{slug}.html', [WebsiteController::class, 'blogPost'])->name('website.blog-post');
Route::get('/blog-post-{id}.html', [WebsiteController::class, 'blogPost']);
Route::get('/tracking', [WebsiteController::class, 'tracking'])->name('website.tracking');
Route::get('/schedules', [WebsiteController::class, 'schedules'])->name('website.schedules');
Route::get('/valuation', [WebsiteController::class, 'valuation'])->name('website.valuation');
Route::get('/security', [WebsiteController::class, 'security'])->name('website.security');
Route::get('/terms', [WebsiteController::class, 'terms'])->name('website.terms');
Route::get('/tracking/lookup', [\Modules\Operations\Http\Controllers\PublicTrackingController::class, 'lookup'])
    ->middleware('throttle:30,1')->name('website.tracking.lookup');


// Dashboard - moved to /app/dashboard
Route::get('/app/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

use Modules\CRM\Http\Controllers\LeadController;

Route::middleware(['auth', 'verified'])->prefix('app/crm')->name('crm.')->group(function () {
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
});

use Modules\Operations\Http\Controllers\ShipmentController;
use Modules\Operations\Http\Controllers\QuoteController;
use Modules\Operations\Http\Controllers\ContainerController;
use Modules\Operations\Http\Controllers\LookupController;

Route::middleware(['auth', 'verified'])->prefix('app/operations')->name('operations.')->group(function () {
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
    Route::post('/shipments/bulk', [ShipmentController::class, 'bulk'])->name('shipments.bulk');
    Route::post('/shipments/import', [ShipmentController::class, 'import'])->name('shipments.import');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.update-status');
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

    Route::get('/containers', [ContainerController::class, 'index'])->name('containers.index');
    Route::post('/containers', [ContainerController::class, 'store'])->name('containers.store');
    Route::get('/containers/{container}', [ContainerController::class, 'show'])->name('containers.show');
    Route::put('/containers/{container}', [ContainerController::class, 'update'])->name('containers.update');
    Route::delete('/containers/{container}', [ContainerController::class, 'destroy'])->name('containers.destroy');
    Route::patch('/containers/{container}/status', [ContainerController::class, 'updateStatus'])->name('containers.update-status');
    Route::post('/containers/{container}/vehicles', [ContainerController::class, 'addVehicles'])->name('containers.add-vehicles');
    Route::delete('/containers/{container}/vehicles/{shipment}', [ContainerController::class, 'removeVehicle'])->name('containers.remove-vehicle');

    Route::get('/catalog', [LookupController::class, 'catalog'])->name('catalog.index');
    Route::get('/lookups/{type}', [LookupController::class, 'suggest'])->name('lookups.suggest');
    Route::post('/lookups/{type}', [LookupController::class, 'store'])->name('lookups.store');
    Route::put('/lookups/{type}/{id}', [LookupController::class, 'update'])->name('lookups.update');
    Route::delete('/lookups/{type}/{id}', [LookupController::class, 'destroy'])->name('lookups.destroy');
    Route::get('/vin/{vin}', [LookupController::class, 'vin'])->name('vin.check');

    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
});

use Modules\Inventory\Http\Controllers\VehicleController;

Route::middleware(['auth', 'verified'])->prefix('app/inventory')->name('inventory.')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::patch('/vehicles/{vehicle}/status', [VehicleController::class, 'updateStatus'])->name('vehicles.update-status');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});

// Auth Routes - Moved to /app/login generally, but preserving names
Route::middleware('guest')->prefix('app')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->prefix('app')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-smtp', [\App\Http\Controllers\SettingsController::class, 'testSmtp'])->name('settings.test-smtp');

    // Tasks Routes
    Route::post('/tasks', [\Modules\Tasks\Http\Controllers\TasksController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [\Modules\Tasks\Http\Controllers\TasksController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [\Modules\Tasks\Http\Controllers\TasksController::class, 'destroy'])->name('tasks.destroy');

    // User Management Routes (Admin only)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('store');
        Route::put('/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Finance Module Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/payments', [\Modules\Finance\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [\Modules\Finance\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
        Route::put('/payments/{payment}', [\Modules\Finance\Http\Controllers\PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [\Modules\Finance\Http\Controllers\PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('/invoices', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/invoices', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::patch('/invoices/{invoice}/status', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'updateStatus'])->name('invoices.update-status');

        Route::post('/shipments/{shipment}/charges', [\Modules\Finance\Http\Controllers\ShipmentChargeController::class, 'store'])->name('charges.store');
        Route::put('/charges/{charge}', [\Modules\Finance\Http\Controllers\ShipmentChargeController::class, 'update'])->name('charges.update');
        Route::delete('/charges/{charge}', [\Modules\Finance\Http\Controllers\ShipmentChargeController::class, 'destroy'])->name('charges.destroy');
    });
});

// Backup download route
Route::get('/app/backups/download/{file}', function ($file) {
    abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->can('manage_settings'), 403);
    $path = storage_path('app/backups/' . basename($file));
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->download($path);
})->middleware(['auth'])->name('backups.download');
