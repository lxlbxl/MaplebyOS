<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class LeadsBySourceChart extends ChartWidget
{
    protected static ?string $heading = 'Leads by Source';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $data = \Modules\CRM\Entities\Lead::query()
            ->selectRaw('COUNT(*) as count, source')
            ->groupBy('source')
            ->get();

        $colors = [
            'website' => '#3b82f6',    // blue
            'referral' => '#10b981',   // emerald
            'direct' => '#8b5cf6',     // violet
            'whatsapp' => '#22c55e',   // green
            'other' => '#6b7280',      // gray
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => $data->map(fn($item) => $colors[$item->source] ?? '#6b7280')->toArray(),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $data->pluck('source')->map(fn($s) => ucfirst($s ?? 'Unknown'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}
