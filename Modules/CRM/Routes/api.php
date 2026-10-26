<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\Api\LeadApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('leads/capture', [LeadApiController::class, 'capture']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('leads', [LeadApiController::class, 'index']);
    });
});
