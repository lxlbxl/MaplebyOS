<?php

namespace Modules\CRM\Filament\Pages;

use Filament\Pages\Page;
use Modules\CRM\Entities\Lead;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;

class LeadBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static string $view = 'crm::filament.pages.lead-board';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Lead Pipeline';

    public array $statuses = [
        'new' => ['label' => 'New', 'color' => 'gray'],
        'contacted' => ['label' => 'Contacted', 'color' => 'yellow'],
        'quote_sent' => ['label' => 'Quote Sent', 'color' => 'blue'],
        'won' => ['label' => 'Won', 'color' => 'green'],
        'lost' => ['label' => 'Lost', 'color' => 'red'],
    ];

    public function getLeadsByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return Lead::where('status', $status)
            ->with(['client', 'assignee'])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function getStatusValue(string $status): float
    {
        return Lead::where('status', $status)->sum('estimated_value');
    }

    public function getStatusCount(string $status): int
    {
        return Lead::where('status', $status)->count();
    }

    #[On('updateLeadStatus')]
    public function updateLeadStatus(int $leadId, string $newStatus): void
    {
        $lead = Lead::find($leadId);

        if (!$lead) {
            Notification::make()->title('Lead not found')->danger()->send();
            return;
        }

        $oldStatus = $lead->status;
        $lead->status = $newStatus;

        if ($newStatus === 'won') {
            $lead->won_at = now();
        } elseif ($newStatus === 'lost') {
            $lead->lost_at = now();
        }

        $lead->save();

        Notification::make()
            ->title('Lead Updated')
            ->body("Moved from {$oldStatus} to {$newStatus}")
            ->success()
            ->send();
    }
}
