<?php

namespace Modules\Reports\Filament\Pages;

use Filament\Pages\Page;

class OperationsPerformance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static string $view = 'reports::filament.pages.operations-performance';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 2;
}
