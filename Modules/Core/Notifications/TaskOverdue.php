<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Tasks\Entities\Task;

class TaskOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task Overdue: ' . $this->task->title)
            ->line('The following task is overdue and requires your attention.')
            ->line('Task: ' . $this->task->title)
            ->line('Due Date: ' . $this->task->due_date->format('M d, Y'))
            ->action('View Task', \Modules\Tasks\Filament\Resources\TaskResource::getUrl('edit', ['record' => $this->task]))
            ->line('Please complete this task as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_overdue',
            'title' => 'Task Overdue',
            'body' => $this->task->title,
            'task_id' => $this->task->id,
        ];
    }
}
