<?php
// Upload to: public/clear_cache.php
// Run via: https://adgod.d20.com.ng/public/clear_cache.php
// DELETE THIS FILE AFTER USE!

header('Content-Type: text/plain');
echo "=== Laravel Cache Clearer ===\n\n";

$projectRoot = dirname(__DIR__) . '/';

// Bootstrap Laravel
require_once $projectRoot . 'vendor/autoload.php';
$app = require_once $projectRoot . 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    // Clear application cache
    Artisan::call('cache:clear');
    echo "✓ Application cache cleared\n";

    // Clear view cache
    Artisan::call('view:clear');
    echo "✓ View cache cleared\n";

    // Clear config cache (if exists)
    Artisan::call('config:clear');
    echo "✓ Config cache cleared\n";

    // Clear route cache (if exists)
    Artisan::call('route:clear');
    echo "✓ Route cache cleared\n";

    echo "\n=== All caches cleared successfully! ===\n";
    echo "\n⚠️ IMPORTANT: Delete this file from your server now!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
