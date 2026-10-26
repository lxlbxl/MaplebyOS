<?php
// Save as: public/laravel_diag.php
// URL: https://adgod.d20.com.ng/public/laravel_diag.php

header('Content-Type: text/plain');
echo "=== Laravel Diagnostics v3 ===\n\n";

$projectRoot = dirname(__DIR__) . '/';

// Bootstrap Laravel for DB access
require_once $projectRoot . 'vendor/autoload.php';
$app = require_once $projectRoot . 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// 1. Check Settings Table
echo "--- 1. Settings Table ---\n";
if (Schema::hasTable('settings')) {
    echo "Table exists: YES\n";
    $columns = Schema::getColumnListing('settings');
    echo "Columns: " . implode(', ', $columns) . "\n";
    $count = DB::table('settings')->count();
    echo "Row count: $count\n";
} else {
    echo "Table exists: NO (CRITICAL - Settings page will 500!)\n";
}

// 2. Check Shipments Table
echo "\n--- 2. Shipments Table ---\n";
if (Schema::hasTable('shipments')) {
    echo "Table exists: YES\n";
    $columns = Schema::getColumnListing('shipments');
    echo "Columns: " . implode(', ', $columns) . "\n";
    $count = DB::table('shipments')->count();
    echo "Row count: $count\n";
} else {
    echo "Table exists: NO (CRITICAL!)\n";
}

// 3. Check Clients Table
echo "\n--- 3. Clients Table ---\n";
if (Schema::hasTable('clients')) {
    echo "Table exists: YES\n";
    $columns = Schema::getColumnListing('clients');
    echo "Columns: " . implode(', ', $columns) . "\n";
    $count = DB::table('clients')->count();
    echo "Row count: $count\n";
} else {
    echo "Table exists: NO (CRITICAL!)\n";
}

// 4. Check Tasks Table (known issue from context)
echo "\n--- 4. Tasks Table ---\n";
if (Schema::hasTable('tasks')) {
    echo "Table exists: YES\n";
    $columns = Schema::getColumnListing('tasks');
    echo "Columns: " . implode(', ', $columns) . "\n";
    // Check for completed_at
    if (in_array('completed_at', $columns)) {
        echo "completed_at column: EXISTS\n";
    } else {
        echo "completed_at column: MISSING (will cause errors!)\n";
    }
} else {
    echo "Table exists: NO\n";
}

// 5. Most Recent Error
echo "\n--- 5. Most Recent Error ---\n";
$logFile = $projectRoot . 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    preg_match_all('/\[(\d{4}-\d{2}-\d{2}[^\]]+)\]\s+(\w+)\.(\w+):\s+([^\n]+)/', $logContent, $matches, PREG_SET_ORDER);

    if (!empty($matches)) {
        $lastError = end($matches);
        echo "Timestamp: " . $lastError[1] . "\n";
        echo "Level: " . $lastError[2] . "." . $lastError[3] . "\n";
        echo "Message: " . $lastError[4] . "\n";

        $lastPos = strrpos($logContent, $lastError[0]);
        if ($lastPos !== false) {
            $errorBlock = substr($logContent, $lastPos, 1500);
            echo "\n--- Error Block ---\n";
            echo $errorBlock;
        }
    } else {
        echo "No parsed errors found.\n";
    }
} else {
    echo "Log file not found.\n";
}

echo "\n\n=== End Diagnostics ===\n";
