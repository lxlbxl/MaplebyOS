<?php

namespace Modules\Reports\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Operations\Entities\Job;

class JobsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Jobs by Status';

    protected function getData(): array
    {
        $data = Job::query()
            ->selectRaw('COUNT(*) as aggregate, status')
            ->groupBy('status')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jobs',
                    'data' => $data->pluck('aggregate')->toArray(),
                    'backgroundColor' => [
                        '#9ca3af', // gray
                        '#3b82f6', // blue
                        '#eab308', // yellow
                        '#10b981', // green
                        '#ef4444', // red
                    ],
                ],
            ],
            'labels' => $data->pluck('status')->map(fn($status) => ucfirst(str_replace('_', ' ', $status)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
