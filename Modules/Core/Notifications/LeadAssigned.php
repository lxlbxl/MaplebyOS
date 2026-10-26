<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\CRM\Entities\Lead;

class LeadAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Lead Assigned: ' . $this->lead->title)
            ->line('You have been assigned a new lead.')
            ->action('View Lead', \Modules\CRM\Filament\Resources\LeadResource::getUrl('edit', ['record' => $this->lead]))
            ->line('Status: ' . $this->lead->status);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'lead_assigned',
            'title' => 'New Lead Assigned',
            'body' => $this->lead->title,
            'lead_id' => $this->lead->id,
        ];
    }
}
