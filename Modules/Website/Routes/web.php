<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\WebsiteController;

/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
|
| These routes serve the static HTML website and handle form submissions.
| The static HTML files are served from the /website folder.
|
*/

/*
// Homepage
Route::get('/', [WebsiteController::class, 'serveStatic'])->defaults('page', 'index')->name('website.home');

// Static pages
Route::get('/about', [WebsiteController::class, 'serveStatic'])->defaults('page', 'about')->name('website.about');
Route::get('/services', [WebsiteController::class, 'serveStatic'])->defaults('page', 'services')->name('website.services');
Route::get('/contact', [WebsiteController::class, 'serveStatic'])->defaults('page', 'contact')->name('website.contact');
Route::get('/process', [WebsiteController::class, 'serveStatic'])->defaults('page', 'process')->name('website.process');
Route::get('/blog', [WebsiteController::class, 'serveStatic'])->defaults('page', 'blog')->name('website.blog');
Route::get('/blog-post-1', [WebsiteController::class, 'serveStatic'])->defaults('page', 'blog-post-1');
Route::get('/blog-post-2', [WebsiteController::class, 'serveStatic'])->defaults('page', 'blog-post-2');
Route::get('/blog-post-3', [WebsiteController::class, 'serveStatic'])->defaults('page', 'blog-post-3');

// Inventory page (serves HTML, uses JS to fetch data via API)
Route::get('/inventory', [WebsiteController::class, 'inventory'])->name('website.inventory');

// Shipment request page
Route::get('/ship', [WebsiteController::class, 'shipmentRequestPage'])->name('website.ship');

// Serve static assets from website folder (CSS, JS)
Route::get('/website/{file}', function ($file) {
    $path = base_path('website/' . $file);
    if (!file_exists($path)) {
        abort(404);
    }

    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response(file_get_contents($path), 200)
        ->header('Content-Type', $mimeType);
})->where('file', '.*')->name('website.assets');
*/
