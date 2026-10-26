<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Operations\Entities\Job;

class ProjectProgressChart extends ChartWidget
{
    protected static ?string $heading = 'Shipments by Mode';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        // Real data query example
        // $counts = Job::selectRaw('service_type, count(*) as total')
        //    ->groupBy('service_type')
        //    ->pluck('total', 'service_type')->toArray();

        // For demonstration (or if DB empty), return Logistics data
        return [
            'datasets' => [
                [
                    'label' => 'Shipments',
                    'data' => [45, 25, 20, 10], // Example data
                    'backgroundColor' => [
                        '#064e3b', // Deep Emerald (Primary)
                        '#10b981', // Emerald (Secondary)
                        '#d1fae5', // Pale Mint (Tertiary)
                        '#f3f4f6', // Light Gray (Quaternary)
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['Completed', 'Running', 'Pending', 'Archived'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
