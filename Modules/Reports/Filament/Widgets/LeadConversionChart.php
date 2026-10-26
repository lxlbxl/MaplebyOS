<?php

namespace Modules\Reports\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\CRM\Entities\Lead;

class LeadConversionChart extends ChartWidget
{
    protected static ?string $heading = 'Lead Status Conversions';

    protected function getData(): array
    {
        $data = Lead::query()
            ->selectRaw('COUNT(*) as aggregate, status')
            ->groupBy('status')
            ->get();

        // Order strictly for funnel: New -> Contacted -> Quote Sent -> Won
        $order = ['new', 'contacted', 'quote_sent', 'won', 'lost'];
        $sortedData = [];
        $labels = [];

        foreach ($order as $status) {
            $record = $data->firstWhere('status', $status);
            $sortedData[] = $record ? $record->aggregate : 0;
            $labels[] = ucfirst(str_replace('_', ' ', $status));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $sortedData,
                    'backgroundColor' => '#8b5cf6', // violet-500
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
