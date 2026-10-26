<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Entities\Lead;
use Modules\Operations\Entities\Shipment;
use Modules\Inventory\Entities\Vehicle;
use Modules\Quoting\Entities\Quote;
use Modules\Tasks\Entities\Task;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $timeRange = $request->input('time_range', '30d'); // lifetime, 12m, ytd, 30d

            // Base Query for Stats
            $query = Shipment::query();

            if ($timeRange === '12m') {
                $query->where('created_at', '>=', now()->subMonths(12));
            } elseif ($timeRange === 'ytd') {
                $query->where('created_at', '>=', now()->startOfYear());
            } elseif ($timeRange === '30d') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
            // lifetime = no filter

            // Aggregate Shipment Stats
            // Clone query to avoid mutating it for subsequent uses
            $statsQuery = clone $query;
            $shipmentStats = $statsQuery->selectRaw("
                count(*) as total,
                sum(total_value) as revenue,
                sum(case when status = 'delivered' then 1 else 0 end) as delivered,
                sum(case when status in ('received', 'scheduled', 'shipped', 'arrived') then 1 else 0 end) as active,
                sum(case when status = 'pending' then 1 else 0 end) as pending_requests
            ")->first();

            $totalRevenue = $shipmentStats->revenue ?? 0;
            $totalShipments = $shipmentStats->total ?? 0;
            $completedShipments = $shipmentStats->delivered ?? 0;
            $activeShipments = $shipmentStats->active ?? 0;
            $pendingRequests = $shipmentStats->pending_requests ?? 0;

            // Top 3 Clients by Shipment Count (Time Filtered)
            // Note: Don't use with() after selectRaw/groupBy - fetch clients separately
            $topClientData = Shipment::query()
                ->when($timeRange === '12m', fn($q) => $q->where('created_at', '>=', now()->subMonths(12)))
                ->when($timeRange === 'ytd', fn($q) => $q->where('created_at', '>=', now()->startOfYear()))
                ->when($timeRange === '30d', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))
                ->selectRaw('client_id, count(*) as total_shipments')
                ->whereNotNull('client_id')
                ->groupBy('client_id')
                ->orderByDesc('total_shipments')
                ->take(3)
                ->get();

            $clientIds = $topClientData->pluck('client_id')->toArray();
            $clients = \Modules\CRM\Entities\Client::whereIn('id', $clientIds)->get()->keyBy('id');

            $topClients = $topClientData->map(function ($item) use ($clients) {
                $client = $clients->get($item->client_id);
                return [
                    'id' => $item->client_id,
                    'name' => $client->company_name ?? $client->contact_person ?? 'Unknown',
                    'count' => $item->total_shipments
                ];
            });

            // Other unrelated stats (Inventory, Leads, etc - usually not time filtered or different context)
            $pendingQuotes = Quote::where('status', 'pending')->count();
            $inventoryCount = Vehicle::count();

            // Active Tasks
            $tasks = Task::with(['assignedTo'])
                ->where('assigned_to', Auth::id())
                ->where('status', '!=', 'completed')
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->take(5)
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'due_date' => $task->due_date ? $task->due_date->format('M d, H:i') : null,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'is_overdue' => $task->due_date && $task->due_date->isPast(),
                        'assigned_by' => $task->assignedBy?->name ?? 'System',
                    ];
                });

            // Completed Tasks History
            $completedTasks = Task::where('assigned_to', Auth::id())
                ->where('status', 'completed')
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'completed_at' => $task->updated_at ? $task->updated_at->format('M d, H:i') : 'Unknown',
                        'completed_by' => 'Unknown',
                    ];
                });

            $users = \App\Models\User::select('id', 'name')->get();

            // Recent Shipments (Always showing most recent regardless of filter, or should it respect filter? Usually recent is recent.)
            // Let's keep recent shipments global for now, as it's a "feed".
            $recentShipments = Shipment::with('client')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($shipment) {
                    return [
                        'id' => $shipment->id,
                        'title' => $shipment->title ?? $shipment->shipment_number,
                        'client_name' => $shipment->client?->company_name ?? $shipment->client?->contact_person ?? 'Unknown',
                        'amount' => $shipment->total_value,
                        'status' => $shipment->status,
                        'date' => $shipment->created_at->format('M d'),
                    ];
                });

            // Calculate Revenue Trend (Month over Month) - independent of time filter
            $currentMonthRevenue = Shipment::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_value');

            $lastMonthRevenue = Shipment::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->sum('total_value');

            if ($lastMonthRevenue > 0) {
                $growth = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
                $trendSymbol = $growth >= 0 ? '+' : '';
                $trendString = $trendSymbol . round($growth, 1) . '% from last month';
            } else {
                $trendString = $currentMonthRevenue > 0 ? '+100% from last month' : 'No change';
            }

            // Greeting Logic
            $generalSettings = \Modules\Core\Entities\Setting::getGroup('general');
            $timezone = $generalSettings['timezone'] ?? 'UTC'; // Fallback to UTC

            try {
                $currentHour = now()->setTimezone($timezone)->hour;
            } catch (\Exception $e) {
                $currentHour = now()->hour; // Fallback if timezone invalid
            }

            if ($currentHour >= 5 && $currentHour < 12) {
                $greeting = 'Good morning';
            } elseif ($currentHour >= 12 && $currentHour < 18) {
                $greeting = 'Good afternoon';
            } else {
                $greeting = 'Good evening';
            }

            return Inertia::render('Dashboard/Index', [
                'auth' => [
                    'user' => Auth::user(),
                ],
                'greeting' => $greeting,
                'users' => $users,
                'filters' => [
                    'time_range' => $timeRange,
                ],
                'stats' => [
                    'revenue' => [
                        'value' => $totalRevenue,
                        'trend' => $trendString,
                    ],
                    'projects' => [
                        'total' => $totalShipments,
                        'ended' => $completedShipments,
                        'running' => $activeShipments,
                        'pending' => $pendingQuotes,
                        'requests' => $pendingRequests,
                    ],
                    'inventory' => [
                        'value' => $inventoryCount,
                    ],
                    'top_clients' => $topClients,
                ],
                'tasks' => $tasks,
                'completedTasks' => $completedTasks,
                'recentShipments' => $recentShipments,
                'analytics' => [
                    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'data' => [12, 19, 3, 5, 2, 3, 9],
                ]
            ]);
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
        }
    }
}
