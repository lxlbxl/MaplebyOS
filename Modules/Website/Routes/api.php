<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\WebsiteController;

/*
|--------------------------------------------------------------------------
| Website API Routes
|--------------------------------------------------------------------------
|
| API routes for the public website forms and data fetching.
| These endpoints are publicly accessible (no auth required).
|
*/

Route::prefix('website')->group(function () {
    // Get inventory vehicles
    Route::get('/inventory', [WebsiteController::class, 'getInventory']);

    // Form submissions
    Route::post('/enquiry', [WebsiteController::class, 'submitEnquiry']);
    Route::post('/quote', [WebsiteController::class, 'submitQuote']);
    Route::post('/shipment', [WebsiteController::class, 'submitShipmentRequest']);
});
