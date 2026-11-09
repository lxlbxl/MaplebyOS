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
Route::redirect('/app', '/app/dashboard');
Route::get('/app/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

use Modules\CRM\Http\Controllers\LeadController;

Route::middleware(['auth', 'verified'])->prefix('app/crm')->name('crm.')->group(function () {
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search')->middleware('permission:customers.view|vehicles.create|vehicles.edit|finance.manage|quotes.manage');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index')->middleware('permission:customers.view');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show')->middleware('permission:customers.view');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store')->middleware('permission:customers.manage');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update')->middleware('permission:customers.manage');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy')->middleware('permission:records.delete');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index')->middleware('permission:leads.manage');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store')->middleware('permission:leads.manage');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update')->middleware('permission:leads.manage');
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status')->middleware('permission:leads.manage');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy')->middleware('permission:records.delete');
});

use Modules\Operations\Http\Controllers\ShipmentController;
use Modules\Operations\Http\Controllers\QuoteController;
use Modules\Operations\Http\Controllers\ContainerController;
use Modules\Operations\Http\Controllers\LookupController;
use Modules\Operations\Http\Controllers\ClearingJobController;
use Modules\Operations\Http\Controllers\ClearingOfficerController;
use Modules\Operations\Http\Controllers\DepositController;
use Modules\Operations\Http\Controllers\BoxController;

Route::middleware(['auth', 'verified'])->prefix('app/operations')->name('operations.')->group(function () {
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index')->middleware('permission:vehicles.view');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show')->middleware('permission:vehicles.view');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store')->middleware('permission:vehicles.create');
    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update')->middleware('permission:vehicles.edit|vehicles.edit_own');
    Route::post('/shipments/bulk', [ShipmentController::class, 'bulk'])->name('shipments.bulk')->middleware('permission:vehicles.bulk');
    Route::post('/shipments/import', [ShipmentController::class, 'import'])->name('shipments.import')->middleware('permission:vehicles.import');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.update-status')->middleware('permission:vehicles.status');
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy')->middleware('permission:records.delete');

    Route::get('/containers', [ContainerController::class, 'index'])->name('containers.index')->middleware('permission:containers.view');
    Route::post('/containers', [ContainerController::class, 'store'])->name('containers.store')->middleware('permission:containers.manage');
    Route::get('/containers/{container}', [ContainerController::class, 'show'])->name('containers.show')->middleware('permission:containers.view');
    Route::put('/containers/{container}', [ContainerController::class, 'update'])->name('containers.update')->middleware('permission:containers.manage');
    Route::delete('/containers/{container}', [ContainerController::class, 'destroy'])->name('containers.destroy')->middleware('permission:records.delete');
    Route::patch('/containers/{container}/status', [ContainerController::class, 'updateStatus'])->name('containers.update-status')->middleware('permission:containers.manage');
    Route::post('/containers/{container}/vehicles', [ContainerController::class, 'addVehicles'])->name('containers.add-vehicles')->middleware('permission:containers.manage');
    Route::delete('/containers/{container}/vehicles/{shipment}', [ContainerController::class, 'removeVehicle'])->name('containers.remove-vehicle')->middleware('permission:containers.manage');

    // General cargo clearing jobs (a client's own containers cleared in Nigeria)
    Route::get('/clearing', [ClearingJobController::class, 'index'])->name('clearing.index')->middleware('permission:cargo.view');
    Route::post('/clearing', [ClearingJobController::class, 'store'])->name('clearing.store')->middleware('permission:cargo.manage');
    Route::get('/clearing/{job}', [ClearingJobController::class, 'show'])->name('clearing.show')->middleware('permission:cargo.view');
    Route::put('/clearing/{job}', [ClearingJobController::class, 'update'])->name('clearing.update')->middleware('permission:cargo.manage');
    Route::patch('/clearing/{job}/status', [ClearingJobController::class, 'updateStatus'])->name('clearing.update-status')->middleware('permission:cargo.status');
    Route::delete('/clearing/{job}', [ClearingJobController::class, 'destroy'])->name('clearing.destroy')->middleware('permission:records.delete');
    Route::post('/clearing/{job}/containers', [ClearingJobController::class, 'addContainer'])->name('clearing.add-container')->middleware('permission:cargo.manage');
    Route::delete('/clearing/{job}/containers/{container}', [ClearingJobController::class, 'removeContainer'])->name('clearing.remove-container')->middleware('permission:cargo.manage');

    // Container deposits paid to shipping lines, and their refunds
    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index')->middleware('permission:deposits.view');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store')->middleware('permission:deposits.manage');
    Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show')->middleware('permission:deposits.view');
    Route::put('/deposits/{deposit}', [DepositController::class, 'update'])->name('deposits.update')->middleware('permission:deposits.manage|deposits.oversee');
    Route::put('/deposits/{deposit}/refund', [DepositController::class, 'updateRefund'])->name('deposits.refund')->middleware('permission:deposits.manage|deposits.oversee');
    Route::post('/deposits/{deposit}/follow-ups', [DepositController::class, 'storeFollowUp'])->name('deposits.follow-ups.store')->middleware('permission:deposits.manage|deposits.oversee');
    Route::post('/deposits/{deposit}/review', [DepositController::class, 'review'])->name('deposits.review')->middleware('permission:deposits.oversee');
    Route::delete('/deposits/{deposit}', [DepositController::class, 'destroy'])->name('deposits.destroy')->middleware('permission:records.delete');

    // Clearing officers: who handles each clearing, reassignments and credit
    Route::get('/clearing-officers', [ClearingOfficerController::class, 'index'])->name('clearing-officers.index')->middleware('permission:clearing.view');
    Route::post('/clearing-officers', [ClearingOfficerController::class, 'store'])->name('clearing-officers.store')->middleware('permission:clearing.manage');
    Route::post('/clearing-officers/assign', [ClearingOfficerController::class, 'assign'])->name('clearing-officers.assign')->middleware('permission:clearing.manage');
    Route::post('/clearing-work', [ClearingOfficerController::class, 'logWork'])->name('clearing-work.store')->middleware('permission:clearing.manage');
    Route::post('/clearing-work/override', [ClearingOfficerController::class, 'override'])->name('clearing-work.override')->middleware('permission:clearing.override');
    Route::get('/clearing-officers/{officer}', [ClearingOfficerController::class, 'show'])->name('clearing-officers.show')->middleware('permission:clearing.view');
    Route::put('/clearing-officers/{officer}', [ClearingOfficerController::class, 'update'])->name('clearing-officers.update')->middleware('permission:clearing.manage');

    // Boxes / lite cargo riding in car containers
    Route::get('/boxes', [BoxController::class, 'index'])->name('boxes.index')->middleware('permission:cargo.view');
    Route::post('/boxes', [BoxController::class, 'store'])->name('boxes.store')->middleware('permission:cargo.manage');
    Route::get('/boxes/{box}', [BoxController::class, 'show'])->name('boxes.show')->middleware('permission:cargo.view');
    Route::put('/boxes/{box}', [BoxController::class, 'update'])->name('boxes.update')->middleware('permission:cargo.manage');
    Route::patch('/boxes/{box}/status', [BoxController::class, 'updateStatus'])->name('boxes.update-status')->middleware('permission:cargo.status');
    Route::delete('/boxes/{box}', [BoxController::class, 'destroy'])->name('boxes.destroy')->middleware('permission:records.delete');

    Route::get('/catalog', [LookupController::class, 'catalog'])->name('catalog.index')->middleware('permission:catalog.manage');
    Route::get('/lookups/{type}', [LookupController::class, 'suggest'])->name('lookups.suggest');
    Route::post('/lookups/{type}', [LookupController::class, 'store'])->name('lookups.store')->middleware('permission:vehicles.create|vehicles.edit|containers.manage|catalog.manage');
    Route::put('/lookups/{type}/{id}', [LookupController::class, 'update'])->name('lookups.update')->middleware('permission:catalog.manage');
    Route::delete('/lookups/{type}/{id}', [LookupController::class, 'destroy'])->name('lookups.destroy')->middleware('permission:catalog.manage');
    Route::get('/vin/{vin}', [LookupController::class, 'vin'])->name('vin.check');

    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index')->middleware('permission:quotes.manage');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store')->middleware('permission:quotes.manage');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show')->middleware('permission:quotes.manage');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update')->middleware('permission:quotes.manage');
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status')->middleware('permission:quotes.manage');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy')->middleware('permission:records.delete');
});

use Modules\Inventory\Http\Controllers\VehicleController;

Route::middleware(['auth', 'verified'])->prefix('app/inventory')->name('inventory.')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index')->middleware('permission:inventory.manage');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store')->middleware('permission:inventory.manage');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update')->middleware('permission:inventory.manage');
    Route::patch('/vehicles/{vehicle}/status', [VehicleController::class, 'updateStatus'])->name('vehicles.update-status')->middleware('permission:inventory.manage');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy')->middleware('permission:records.delete');
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

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index')->middleware('permission:manage_settings');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update')->middleware('permission:manage_settings');
    Route::post('/settings/test-smtp', [\App\Http\Controllers\SettingsController::class, 'testSmtp'])->name('settings.test-smtp')->middleware('permission:manage_settings');

    // Tasks Routes
    Route::post('/tasks', [\Modules\Tasks\Http\Controllers\TasksController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [\Modules\Tasks\Http\Controllers\TasksController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [\Modules\Tasks\Http\Controllers\TasksController::class, 'destroy'])->name('tasks.destroy');

    // User Management Routes (Admin only)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index')->middleware('permission:manage_users');
        Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('store')->middleware('permission:manage_users');
        Route::put('/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('update')->middleware('permission:manage_users');
        Route::delete('/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('destroy')->middleware('permission:manage_users');
        Route::patch('/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:manage_users');
    });

    // Finance Module Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/payments', [\Modules\Finance\Http\Controllers\PaymentController::class, 'index'])->name('payments.index')->middleware('permission:finance.manage');
        Route::post('/payments', [\Modules\Finance\Http\Controllers\PaymentController::class, 'store'])->name('payments.store')->middleware('permission:finance.manage');
        Route::put('/payments/{payment}', [\Modules\Finance\Http\Controllers\PaymentController::class, 'update'])->name('payments.update')->middleware('permission:finance.manage');
        Route::delete('/payments/{payment}', [\Modules\Finance\Http\Controllers\PaymentController::class, 'destroy'])->name('payments.destroy')->middleware('permission:records.delete');

        Route::get('/invoices', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index')->middleware('permission:finance.manage');
        Route::post('/invoices', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store')->middleware('permission:finance.manage');
        Route::get('/invoices/{invoice}', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show')->middleware('permission:finance.manage');
        Route::patch('/invoices/{invoice}/status', [\Modules\Finance\Http\Controllers\InvoiceController::class, 'updateStatus'])->name('invoices.update-status')->middleware('permission:finance.manage');

        Route::post('/shipments/{shipment}/charges', [\Modules\Finance\Http\Controllers\ShipmentChargeController::class, 'store'])->name('charges.store')->middleware('permission:finance.manage|money.edit_costs');
        Route::put('/charges/{charge}', [\Modules\Finance\Http\Controllers\ShipmentChargeController::class, 'update'])->name('charges.update')->middleware('permission:finance.manage|money.edit_costs');
        Route::delete('/charges/{charge}', [\Modules\Finance\Http\Controllers\ShipmentChargeController::class, 'destroy'])->name('charges.destroy')->middleware('permission:finance.manage|money.edit_costs');
    });
});

// Scanned / uploaded documents on cars and containers
Route::middleware(['auth', 'verified'])->prefix('app/documents')->name('documents.')->group(function () {
    Route::post('/', [\Modules\Operations\Http\Controllers\DocumentController::class, 'store'])->name('store')->middleware('permission:documents.upload');
    Route::get('/{document}', [\Modules\Operations\Http\Controllers\DocumentController::class, 'show'])->name('show');
    Route::delete('/{document}', [\Modules\Operations\Http\Controllers\DocumentController::class, 'destroy'])->name('destroy')->middleware('permission:records.delete');
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
