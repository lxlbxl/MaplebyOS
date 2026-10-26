<?php

namespace Modules\Reports\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Operations\Entities\Job;
use Illuminate\Support\Facades\Cache;

class SalesByRepChart extends ChartWidget
{
    protected static ?string $heading = 'Sales by Representative';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        return Cache::remember('report_sales_by_rep', 300, function () {
            $data = Job::query()
                ->selectRaw('COUNT(*) as aggregate, assigned_to')
                ->whereNotNull('assigned_to')
                ->groupBy('assigned_to')
                ->with('assignee')
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Jobs Sold',
                        'data' => $data->pluck('aggregate')->toArray(),
                        'backgroundColor' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                    ],
                ],
                'labels' => $data->map(fn($job) => $job->assignee?->name ?? 'Unknown')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
