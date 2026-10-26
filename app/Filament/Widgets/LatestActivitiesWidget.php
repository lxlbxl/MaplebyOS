<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Spatie\Activitylog\Models\Activity;

class LatestActivitiesWidget extends Widget
{
    protected static string $view = 'filament.widgets.latest-activities-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    public function getActivities()
    {
        // Return dummy data if table doesn't exist yet or just try to fetch
        try {
            return Activity::latest()->take(10)->get();
        } catch (\Exception $e) {
            return [
                (object) [
                    'causer' => (object) ['name' => 'John Doe'],
                    'description' => 'created Lead #L-1234',
                    'created_at' => now()->subMinutes(5),
                ],
                (object) [
                    'causer' => (object) ['name' => 'Sarah Smith'],
                    'description' => "updated Job #J-5678 to 'Shipped'",
                    'created_at' => now()->subHour(),
                ],
                (object) [
                    'causer' => (object) ['name' => 'System'],
                    'description' => "imported 50 inventory items",
                    'created_at' => now()->subHours(2),
                ],
            ];
        }
    }
}
