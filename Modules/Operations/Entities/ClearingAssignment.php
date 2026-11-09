<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * One officer's spell on a clearing. The open row (ended_at null) is the
 * current officer; ended rows are the history of reassignments.
 */
class ClearingAssignment extends Model
{
    protected $fillable = ['clearable_type', 'clearable_id', 'clearing_officer_id', 'started_at', 'ended_at', 'assigned_by', 'ended_by', 'reason'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /** A car container, or a general cargo clearing job (Shipment). */
    public function clearable()
    {
        return $this->morphTo();
    }

    public function officer()
    {
        return $this->belongsTo(ClearingOfficer::class, 'clearing_officer_id')->withTrashed();
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function ender()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function work()
    {
        return $this->hasMany(ClearingWork::class, 'assignment_id');
    }
}
