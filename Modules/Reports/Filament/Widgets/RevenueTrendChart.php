<?php

namespace Modules\Reports\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Operations\Entities\Job;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RevenueTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        return Cache::remember('report_revenue_trend', 300, function () {
            $data = Job::query()
                ->selectRaw('SUM(total_value) as aggregate, strftime("%Y-%m", created_at) as month')
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Revenue ($)',
                        'data' => $data->pluck('aggregate')->toArray(),
                        'borderColor' => '#10b981',
                        'fill' => 'start',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                    ],
                ],
                'labels' => $data->pluck('month')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
