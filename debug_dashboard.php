<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Operations\Entities\Shipment;
use Modules\Quoting\Entities\Quote;
use Modules\Tasks\Entities\Task;
use Modules\Inventory\Entities\Vehicle;
use Illuminate\Support\Facades\DB;

function profile($name, $callback)
{
    $start = microtime(true);
    try {
        $callback();
        $end = microtime(true);
        echo "[$name] SUCCESS: " . round($end - $start, 4) . "s\n";
    } catch (\Throwable $e) {
        $end = microtime(true);
        echo "[$name] FAILED: " . round($end - $start, 4) . "s - Error: " . $e->getMessage() . "\n";
    }
}

echo "Starting profiling...\n";

profile('Base Query', function () {
    Shipment::query()->count();
});

profile('Shipment Stats', function () {
    Shipment::selectRaw("
        count(*) as total,
        sum(total_value) as revenue,
        sum(case when status = 'delivered' then 1 else 0 end) as delivered,
        sum(case when status in ('received', 'scheduled', 'shipped', 'arrived') then 1 else 0 end) as active,
        sum(case when status = 'pending' then 1 else 0 end) as pending_requests
    ")->first();
});

profile('Top Clients', function () {
    Shipment::selectRaw('client_id, count(*) as total_shipments')
        ->whereNotNull('client_id')
        ->groupBy('client_id')
        ->orderByDesc('total_shipments')
        ->take(3)
        ->with('client:id,company_name,contact_person')
        ->get();
});

profile('Pending Quotes', function () {
    Quote::where('status', 'pending')->count();
});

profile('Inventory Count', function () {
    Vehicle::count();
});

profile('Tasks', function () {
    Task::take(5)->get();
});

profile('Revenue This Month', function () {
    Shipment::whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('total_value');
});

profile('Settings', function () {
    \Modules\Core\Entities\Setting::getGroup('general');
});

echo "Profiling finished.\n";
