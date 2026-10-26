<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Current Period
        $currentMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Jobs - Current vs Last Month
        $currentJobs = \Modules\Operations\Entities\Shipment::where('created_at', '>=', $currentMonthStart)->count();
        $lastMonthJobs = \Modules\Operations\Entities\Shipment::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $jobsTrend = $lastMonthJobs > 0 ? round((($currentJobs - $lastMonthJobs) / $lastMonthJobs) * 100) : 100;

        // Active Jobs
        $activeJobs = \Modules\Operations\Entities\Shipment::where('status', 'in_progress')->count();

        // Quotes
        $pendingQuotes = \Modules\Quoting\Entities\Quote::whereIn('status', ['draft', 'sent'])->count();
        $currentMonthQuotes = \Modules\Quoting\Entities\Quote::where('created_at', '>=', $currentMonthStart)->count();
        $lastMonthQuotes = \Modules\Quoting\Entities\Quote::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $quotesTrend = $lastMonthQuotes > 0 ? round((($currentMonthQuotes - $lastMonthQuotes) / $lastMonthQuotes) * 100) : 100;

        // Revenue
        $currentRevenue = \Modules\Operations\Entities\Shipment::where('created_at', '>=', $currentMonthStart)->sum('total_value');
        $lastMonthRevenue = \Modules\Operations\Entities\Shipment::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_value');
        $revenueTrend = $lastMonthRevenue > 0 ? round((($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100) : 100;

        return [
            Stat::make('Monthly Revenue', '$' . number_format($currentRevenue, 0))
                ->extraAttributes([
                    'class' => 'fi-wi-stats-overview-stat-accent',
                ])
                ->description($this->getTrendDescription($revenueTrend, 'vs last month'))
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getRevenueSparkline())
                ->color('success'),

            Stat::make('Active Jobs', $activeJobs)
                ->description($this->getTrendDescription($jobsTrend, 'new this month'))
                ->descriptionIcon('heroicon-m-play-circle')
                ->chart($this->getJobsSparkline())
                ->color('success'),

            Stat::make('Pending Quotes', $pendingQuotes)
                ->description($this->getTrendDescription($quotesTrend, 'quote activity'))
                ->descriptionIcon('heroicon-m-clock')
                ->chart($this->getQuotesSparkline())
                ->color('success'),

            Stat::make('New Leads', \Modules\CRM\Entities\Lead::where('created_at', '>=', $currentMonthStart)->count())
                ->description('This month growth')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
        ];
    }

    protected function getTrendDescription(int $trend, string $suffix): string
    {
        $sign = $trend >= 0 ? '+' : '';
        return "{$sign}{$trend}% {$suffix}";
    }

    protected function getRevenueSparkline(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = \Modules\Operations\Entities\Shipment::whereDate('created_at', $date)->sum('total_value') / 1000;
        }
        return $data;
    }

    protected function getJobsSparkline(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = \Modules\Operations\Entities\Shipment::whereDate('created_at', $date)->count();
        }
        return $data;
    }

    protected function getQuotesSparkline(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = \Modules\Quoting\Entities\Quote::whereDate('created_at', $date)->count();
        }
        return $data;
    }
}
