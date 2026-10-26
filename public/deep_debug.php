<?php
// Upload to: public/deep_debug.php
// URL: https://adgod.d20.com.ng/public/deep_debug.php
// DELETE THIS FILE AFTER USE!

header('Content-Type: text/plain');
echo "=== Deep Debug v1 ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$projectRoot = dirname(__DIR__) . '/';

// Bootstrap Laravel
require_once $projectRoot . 'vendor/autoload.php';
$app = require_once $projectRoot . 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Entities\Setting;

// 1. Check Settings Table Structure
echo "--- 1. Settings Table Structure ---\n";
if (Schema::hasTable('settings')) {
    $columns = Schema::getColumnListing('settings');
    echo "Columns: " . implode(', ', $columns) . "\n";

    // Check if 'id' column exists (required for updateOrCreate)
    if (!in_array('id', $columns)) {
        echo "WARNING: 'id' column MISSING! This will cause updateOrCreate to fail.\n";
    } else {
        echo "ID column: EXISTS\n";
    }

    // Show current settings
    echo "\nCurrent settings:\n";
    $settings = DB::table('settings')->get();
    foreach ($settings as $s) {
        echo "  - {$s->key} = {$s->value} (type: {$s->type}, group: {$s->group})\n";
    }
} else {
    echo "Table does NOT exist!\n";
}

// 2. Test Setting::set() method
echo "\n--- 2. Test Setting::set() ---\n";
try {
    Setting::set('test_key', 'test_value_' . time(), 'string', 'general');
    echo "Setting::set() SUCCESS\n";

    $testValue = Setting::get('test_key');
    echo "Setting::get() returned: $testValue\n";

    // Clean up test
    DB::table('settings')->where('key', 'test_key')->delete();
    echo "Test key cleaned up.\n";
} catch (Exception $e) {
    echo "Setting::set() FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 3. Check for Missing Columns in Related Tables
echo "\n--- 3. Related Table Checks ---\n";

// Check clients table for is_active
$clientColumns = Schema::getColumnListing('clients');
echo "clients.is_active: " . (in_array('is_active', $clientColumns) ? "EXISTS" : "MISSING") . "\n";

// Check shipments table for required columns
$shipmentColumns = Schema::getColumnListing('shipments');
echo "shipments.status: " . (in_array('status', $shipmentColumns) ? "EXISTS" : "MISSING") . "\n";
echo "shipments.client_id: " . (in_array('client_id', $shipmentColumns) ? "EXISTS" : "MISSING") . "\n";

// 4. Get LATEST Errors (since cache was cleared)
echo "\n--- 4. Latest Errors (Last 10 minutes) ---\n";
$logFile = $projectRoot . 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);

    // Find all errors
    preg_match_all('/\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+([^\n]+)/', $logContent, $matches, PREG_SET_ORDER);

    if (!empty($matches)) {
        // Get errors from last 10 minutes
        $cutoff = strtotime('-10 minutes');
        $recentErrors = [];

        foreach ($matches as $match) {
            $timestamp = strtotime($match[1]);
            if ($timestamp > $cutoff) {
                $recentErrors[] = $match;
            }
        }

        if (!empty($recentErrors)) {
            echo "Found " . count($recentErrors) . " recent errors:\n\n";
            foreach (array_slice($recentErrors, -5) as $error) {
                echo "[{$error[1]}] {$error[2]}.{$error[3]}: {$error[4]}\n";

                // Get more context for this error
                $pos = strrpos($logContent, $error[0]);
                if ($pos !== false) {
                    $block = substr($logContent, $pos, 800);
                    echo "Context: " . $block . "\n\n";
                }
            }
        } else {
            echo "No recent errors found in log.\n";
            echo "TIP: Try to reproduce the error, then run this script again.\n";
        }
    } else {
        echo "Could not parse log file.\n";
    }
} else {
    echo "Log file not found.\n";
}

// 5. Test Inertia Response
echo "\n--- 5. Check Inertia Setup ---\n";
try {
    $inertiaMiddleware = $projectRoot . 'app/Http/Middleware/HandleInertiaRequests.php';
    if (file_exists($inertiaMiddleware)) {
        echo "HandleInertiaRequests.php: EXISTS\n";
    } else {
        echo "HandleInertiaRequests.php: MISSING\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "⚠️ DELETE THIS FILE NOW!\n";
