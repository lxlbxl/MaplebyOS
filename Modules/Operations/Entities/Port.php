<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Operations\Entities\Concerns\HasAliases;

class Port extends Model
{
    use HasAliases;

    protected $fillable = ['country', 'kind'];
}
