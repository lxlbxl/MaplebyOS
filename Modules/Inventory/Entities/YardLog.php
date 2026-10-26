<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

class YardLog extends Model
{
    protected $fillable = [
        'inventory_type',
        'inventory_id',
        'status',
        'logged_by',
        'location',
        'notes',
        'logged_at',
    ];

    public function inventory()
    {
        return $this->morphTo();
    }

    public function logger()
    {
        return $this->belongsTo(\App\Models\User::class, 'logged_by');
    }
}
