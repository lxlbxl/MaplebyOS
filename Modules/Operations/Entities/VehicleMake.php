<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Operations\Entities\Concerns\HasAliases;

class VehicleMake extends Model
{
    use HasAliases;

    public function models()
    {
        return $this->hasMany(VehicleModel::class)->orderBy('name');
    }
}
