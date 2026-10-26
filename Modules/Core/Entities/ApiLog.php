<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $table = 'api_logs';

    protected $fillable = [
        'method',
        'endpoint',
        'ip_address',
        'request_payload',
        'response_status',
        'response_payload',
        'duration_ms',
        'api_key',
    ];
}
