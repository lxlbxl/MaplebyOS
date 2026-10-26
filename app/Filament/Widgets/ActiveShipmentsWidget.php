<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Modules\Operations\Entities\Job;

class ActiveShipmentsWidget extends Widget
{
    // Rename logic to Active Jobs/Shipments
    protected static ?string $title = 'Active Shipments';

    protected static string $view = 'filament.widgets.my-tasks-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 2;

    public array $shipments = [];

    public function mount()
    {
        // Fetch real active jobs
        // Fallback to dummy data if DB is empty for demo
        try {
            $jobs = Job::where('status', 'in_progress')->take(5)->get();

            if ($jobs->count() > 0) {
                // Map jobs to display format
                $this->shipments = $jobs->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'title' => $job->job_number . ' - ' . ($job->origin ?? 'Origin') . ' to ' . ($job->destination ?? 'Dest'),
                        'subtitle' => 'ETA: ' . ($job->estimated_completion ? $job->estimated_completion->format('M d, Y') : 'TBD'),
                        'icon' => 'heroicon-m-truck',
                        'color' => 'text-blue-600 bg-blue-100',
                    ];
                })->toArray();
            } else {
                throw new \Exception('No jobs');
            }
        } catch (\Exception $e) {
            // Demo data
            $this->shipments = [
                [
                    'id' => 1,
                    'title' => 'SHP-2024-001: Lagos -> Toronto',
                    'subtitle' => 'ETA: Dec 12, 2024',
                    'icon' => 'heroicon-m-paper-airplane',
                    'color' => 'text-blue-600 bg-blue-100',
                ],
                [
                    'id' => 2,
                    'title' => 'SHP-2024-045: 2022 Toyota Highlander',
                    'subtitle' => 'Customs Clearing - Pending',
                    'icon' => 'heroicon-m-clipboard-document-check',
                    'color' => 'text-amber-600 bg-amber-100',
                ],
                [
                    'id' => 3,
                    'title' => 'SHP-2024-089: Heavy Equipment Export',
                    'subtitle' => 'In Transit - Arriving Soon',
                    'icon' => 'heroicon-m-truck',
                    'color' => 'text-emerald-600 bg-emerald-100',
                ],
                [
                    'id' => 4,
                    'title' => 'SHP-2024-102: Container #MSKU123456',
                    'subtitle' => 'Vessel: MAERSK SEALAND',
                    'icon' => 'heroicon-m-globe-americas',
                    'color' => 'text-indigo-600 bg-indigo-100',
                ],
            ];
        }
    }
}
