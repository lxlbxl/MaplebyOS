<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \Modules\Tasks\Filament\Widgets\MyTasksWidget::class,
            \App\Filament\Widgets\ProjectProgressChart::class,
            \App\Filament\Widgets\TeamCollaborationWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }
}
