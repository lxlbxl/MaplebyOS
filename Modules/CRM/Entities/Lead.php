<?php

namespace Modules\CRM\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'source',
        'service_type',
        'status',
        'priority',
        'title',
        'description',
        'estimated_value',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website_form_data',
        'assigned_to',
        'lost_reason',
        'won_at',
        'lost_at',
        'next_follow_up',
    ];

    protected $casts = [
        'website_form_data' => 'array',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'next_follow_up' => 'datetime',
        'estimated_value' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function communications()
    {
        return $this->morphMany(\Modules\Communications\Entities\Communication::class, 'communicable');
    }
}
