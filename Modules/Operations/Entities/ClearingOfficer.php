<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A clearing officer (CO): the person who clears a container through
 * customs and the terminal in Nigeria. May be staff or an outside agent.
 */
class ClearingOfficer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'company', 'licence_number', 'user_id', 'is_active', 'notes', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function assignments()
    {
        return $this->hasMany(ClearingAssignment::class)->latest('started_at')->latest('id');
    }

    public function currentAssignments()
    {
        return $this->hasMany(ClearingAssignment::class)->whereNull('ended_at');
    }

    /** Work credited to this officer (after any overrides). */
    public function work()
    {
        return $this->hasMany(ClearingWork::class)->latest('occurred_at')->latest('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
