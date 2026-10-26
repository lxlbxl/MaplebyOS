<?php

namespace Modules\Quoting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type',
        'line_items',
        'terms',
    ];

    protected $casts = [
        'line_items' => 'array',
    ];
}
