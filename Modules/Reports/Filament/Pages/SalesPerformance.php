<?php

namespace Modules\Reports\Filament\Pages;

use Filament\Pages\Page;

class SalesPerformance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'reports::filament.pages.sales-performance';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;
}
