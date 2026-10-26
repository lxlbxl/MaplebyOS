<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class SalesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Show for users with 'sales' role or admins
        $user = auth()->user();
        if (!$user)
            return false;

        // Check if user has sales-related role
        return $user->hasAnyRole(['admin', 'sales', 'super_admin'])
            || $user->email === 'admin@example.com'; // Fallback for testing
    }

    protected function getStats(): array
    {
        $currentMonthStart = Carbon::now()->startOfMonth();

        // Lead Pipeline
        $newLeads = \Modules\CRM\Entities\Lead::where('status', 'new')->count();
        $contactedLeads = \Modules\CRM\Entities\Lead::where('status', 'contacted')->count();
        $quoteSentLeads = \Modules\CRM\Entities\Lead::where('status', 'quote_sent')->count();
        $wonLeads = \Modules\CRM\Entities\Lead::where('status', 'won')
            ->where('won_at', '>=', $currentMonthStart)
            ->count();

        // Pipeline Value
        $pipelineValue = \Modules\CRM\Entities\Lead::whereNotIn('status', ['won', 'lost'])
            ->sum('estimated_value');

        // My Quotes (current user)
        $userId = auth()->id();
        $myQuotes = \Modules\Quoting\Entities\Quote::where('created_by', $userId)
            ->whereIn('status', ['draft', 'sent'])
            ->count();

        // Conversion Rate
        $totalLeads = \Modules\CRM\Entities\Lead::where('created_at', '>=', $currentMonthStart)->count();
        $convertedLeads = \Modules\CRM\Entities\Lead::where('status', 'won')
            ->where('won_at', '>=', $currentMonthStart)
            ->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        return [
            Stat::make('Lead Pipeline', $newLeads + $contactedLeads + $quoteSentLeads)
                ->description("New: {$newLeads} | Contacted: {$contactedLeads} | Quoted: {$quoteSentLeads}")
                ->descriptionIcon('heroicon-m-funnel')
                ->color('primary'),

            Stat::make('Pipeline Value', '$' . number_format($pipelineValue, 0))
                ->description('Total estimated value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Won This Month', $wonLeads)
                ->description("{$conversionRate}% conversion rate")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('My Open Quotes', $myQuotes)
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),
        ];
    }

    protected function getHeading(): ?string
    {
        return 'Sales Dashboard';
    }
}
