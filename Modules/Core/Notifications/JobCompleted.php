<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Operations\Entities\Job;

class JobCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Job Completed: ' . $this->job->job_number)
            ->line('A job has been marked as completed.')
            ->line('Job Number: ' . $this->job->job_number)
            ->line('Client: ' . ($this->job->client?->company_name ?? 'N/A'))
            ->action('View Job', \Modules\Operations\Filament\Resources\JobResource::getUrl('edit', ['record' => $this->job]))
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_completed',
            'title' => 'Job Completed',
            'body' => $this->job->job_number,
            'job_id' => $this->job->id,
        ];
    }
}
