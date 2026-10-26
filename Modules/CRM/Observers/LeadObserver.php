<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Entities\Lead;
use Modules\Core\Notifications\LeadAssigned;

class LeadObserver
{
    public function updated(Lead $lead): void
    {
        // Check if assigned_to was changed
        if ($lead->isDirty('assigned_to') && $lead->assigned_to) {
            $assignee = $lead->assignee;
            if ($assignee) {
                $assignee->notify(new LeadAssigned($lead));
            }
        }
    }
}
