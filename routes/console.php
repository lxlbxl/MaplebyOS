<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Tasks
Schedule::command('backup:run --only-db')->daily()->at('02:00');
Schedule::command('backup:clean')->daily()->at('03:00');

// Check for overdue tasks and send notifications
Schedule::call(function () {
    $overdueTasks = \Modules\Tasks\Entities\Task::query()
        ->where('due_date', '<', now())
        ->where('status', '!=', 'completed')
        ->whereNull('notified_at')
        ->with('assignee')
        ->get();

    foreach ($overdueTasks as $task) {
        if ($task->assignee) {
            $task->assignee->notify(new \Modules\Core\Notifications\TaskOverdue($task));
            $task->update(['notified_at' => now()]);
        }
    }
})->hourly();
