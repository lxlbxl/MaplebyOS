<?php

namespace Modules\Reports\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Entities\Lead;

/** Leads won per sales rep. (Replaces the old "jobs sold" chart.) */
class SalesByRepChart extends ChartWidget
{
    protected static ?string $heading = 'Leads won by rep';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        return Cache::remember('report_sales_by_rep_v2', 300, function () {
            $data = Lead::query()
                ->selectRaw('COUNT(*) as aggregate, assigned_to')
                ->where('status', 'won')
                ->whereNotNull('assigned_to')
                ->groupBy('assigned_to')
                ->with('assignee:id,name')
                ->orderByDesc('aggregate')
                ->get();

            return [
                'datasets' => [[
                    'label' => 'Leads won',
                    'data' => $data->pluck('aggregate')->map(fn ($v) => (int) $v)->all(),
                    'backgroundColor' => ['#ED1722', '#242424', '#C5A46D', '#A80F1B', '#8C8F94', '#393B3E'],
                ]],
                'labels' => $data->map(fn ($row) => $row->assignee?->name ?? 'Unassigned')->all(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
