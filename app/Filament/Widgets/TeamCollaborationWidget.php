<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TeamCollaborationWidget extends Widget
{
    // Rename Logic to Recent Inventory
    protected static ?string $heading = 'Team Collaboration';

    protected static string $view = 'filament.widgets.team-collaboration-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    public array $items = [
        [
            'name' => 'Alexandra Deff',
            'detail' => 'Working on Github Project Repository',
            'status' => 'Completed',
            'status_color' => 'bg-emerald-50 text-emerald-600',
            'image' => 'https://i.pravatar.cc/150?u=alexandra',
        ],
        [
            'name' => 'Edwin Adenike',
            'detail' => 'Working on Integrate User Authentication System',
            'status' => 'In Progress',
            'status_color' => 'bg-amber-50 text-amber-600',
            'image' => 'https://i.pravatar.cc/150?u=edwin',
        ],
        [
            'name' => 'Isaac Oluwatemilorun',
            'detail' => 'Working on Develop Search and Filter Functionality',
            'status' => 'Pending',
            'status_color' => 'bg-rose-50 text-rose-600',
            'image' => 'https://i.pravatar.cc/150?u=isaac',
        ],
        [
            'name' => 'David Oshodi',
            'detail' => 'Working on Responsive Layout for Homepage',
            'status' => 'In Progress',
            'status_color' => 'bg-amber-50 text-amber-600',
            'image' => 'https://i.pravatar.cc/150?u=david',
        ],
    ];
}
