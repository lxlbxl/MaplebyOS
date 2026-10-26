<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Operations\Entities\Concerns\HasAliases;

class VehicleModel extends Model
{
    use HasAliases;

    protected $fillable = ['vehicle_make_id'];

    public function make()
    {
        return $this->belongsTo(VehicleMake::class, 'vehicle_make_id');
    }
}
