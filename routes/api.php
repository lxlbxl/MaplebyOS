<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

/*
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
*/

Route::prefix('website')->group(function () {
    Route::get('/inventory', [\Modules\Inventory\Http\Controllers\WebsiteInventoryController::class, 'index']);
    Route::post('/shipment-request', [\Modules\Operations\Http\Controllers\WebsiteShipmentController::class, 'store']);
});
