<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * A piece of clearing work (a stage reached, a document filed, a deposit
 * paid, a note) credited to the officer holding the clearing at the time.
 *
 * original_officer_id never changes. clearing_officer_id only changes through
 * an override, which records who did it, when and why.
 */
class ClearingWork extends Model
{
    protected $table = 'clearing_work';

    protected $fillable = [
        'clearable_type', 'clearable_id', 'clearing_officer_id', 'original_officer_id', 'assignment_id', 'action',
        'description', 'subject_type', 'subject_id', 'user_id', 'occurred_at', 'overridden_by', 'overridden_at', 'override_reason',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'overridden_at' => 'datetime',
    ];

    public const ACTIONS = [
        'status' => 'Stage',
        'document' => 'Document',
        'paperwork' => 'Paperwork',
        'deposit' => 'Deposit',
        'refund' => 'Refund',
        'note' => 'Work note',
    ];

    public function clearable()
    {
        return $this->morphTo();
    }

    public function officer()
    {
        return $this->belongsTo(ClearingOfficer::class, 'clearing_officer_id')->withTrashed();
    }

    public function originalOfficer()
    {
        return $this->belongsTo(ClearingOfficer::class, 'original_officer_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function overrider()
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    public function isOverridden(): bool
    {
        return $this->overridden_at !== null;
    }
}
