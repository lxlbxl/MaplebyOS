<?php

namespace Modules\Reports\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Operations\Entities\Shipment;

/** Vehicles in the pipeline, by status. (Replaces the old "jobs" chart.) */
class JobsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Vehicles by status';

    protected function getData(): array
    {
        $labels = Shipment::statusesFor(Shipment::CARGO_VEHICLE);
        $counts = Shipment::vehicles()->selectRaw('COUNT(*) as aggregate, status')->groupBy('status')->pluck('aggregate', 'status');
        // Keep pipeline order.
        $rows = collect($labels)->filter(fn ($label, $status) => ($counts[$status] ?? 0) > 0);

        return [
            'datasets' => [[
                'label' => 'Vehicles',
                'data' => $rows->keys()->map(fn ($s) => (int) $counts[$s])->values()->all(),
                'backgroundColor' => ['#DDDEDF', '#C5A46D', '#DCC499', '#8C8F94', '#F4959A', '#ED1722', '#A80F1B', '#393B3E', '#242424', '#16A34A', '#62656A', '#171819'],
            ]],
            'labels' => $rows->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
