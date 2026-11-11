<?php

namespace Modules\Reports\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Modules\Finance\Entities\Payment;

/** Money received per month over the last 6 months, in naira. */
class RevenueTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Payments received (₦)';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        return Cache::remember('report_revenue_trend_v2', 300, function () {
            $start = Carbon::now()->startOfMonth()->subMonths(5);
            $sums = Payment::query()
                ->selectRaw("SUM(amount) as aggregate, DATE_FORMAT(payment_date, '%Y-%m') as month")
                ->where('payment_date', '>=', $start)
                ->groupBy('month')
                ->pluck('aggregate', 'month');

            $months = collect(range(0, 5))->map(fn ($i) => $start->copy()->addMonths($i));

            return [
                'datasets' => [[
                    'label' => 'Payments (₦)',
                    'data' => $months->map(fn ($m) => round((float) ($sums[$m->format('Y-m')] ?? 0), 2))->all(),
                    'borderColor' => '#ED1722',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(237, 23, 34, 0.08)',
                    'tension' => 0.4,
                ]],
                'labels' => $months->map(fn ($m) => $m->format('M Y'))->all(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
