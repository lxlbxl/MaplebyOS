<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class OperationsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Show for users with 'operations' role or admins
        $user = auth()->user();
        if (!$user)
            return false;

        return $user->hasAnyRole(['admin', 'operations', 'super_admin', 'yard_manager'])
            || $user->email === 'admin@example.com';
    }

    protected function getStats(): array
    {
        // Active Jobs by Status
        $inProgressJobs = \Modules\Operations\Entities\Job::where('status', 'in_progress')->count();
        $pendingJobs = \Modules\Operations\Entities\Job::whereIn('status', ['new', 'pending'])->count();
        $onHoldJobs = \Modules\Operations\Entities\Job::where('status', 'on_hold')->count();

        // Yard Capacity
        $vehiclesInYard = \Modules\Inventory\Entities\Vehicle::where('status', 'in_yard')->count();
        $totalVehicles = \Modules\Inventory\Entities\Vehicle::whereNotIn('status', ['sold', 'shipped'])->count();

        // Overdue Tasks
        $overdueTasks = \Modules\Tasks\Entities\Task::where('due_date', '<', Carbon::now())
            ->where('status', '!=', 'completed')
            ->count();

        // Jobs completed this week
        $completedThisWeek = \Modules\Operations\Entities\Job::where('status', 'completed')
            ->where('actual_completion', '>=', Carbon::now()->startOfWeek())
            ->count();

        return [
            Stat::make('Active Jobs', $inProgressJobs)
                ->description("Pending: {$pendingJobs} | On Hold: {$onHoldJobs}")
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Yard Capacity', "{$vehiclesInYard} vehicles")
                ->description("Total inventory: {$totalVehicles}")
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('success'),

            Stat::make('Overdue Tasks', $overdueTasks)
                ->description($overdueTasks > 0 ? 'Requires attention!' : 'All caught up')
                ->descriptionIcon($overdueTasks > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),

            Stat::make('Completed This Week', $completedThisWeek)
                ->description('Jobs finished')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
        ];
    }

    protected function getHeading(): ?string
    {
        return 'Operations Dashboard';
    }
}
