<?php

namespace Modules\Communications\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'communicable_id',
        'communicable_type',
        'type',
        'direction',
        'subject',
        'message',
        'contacted_by',
        'contacted_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function communicable()
    {
        return $this->morphTo();
    }

    public function contactedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'contacted_by');
    }
}
