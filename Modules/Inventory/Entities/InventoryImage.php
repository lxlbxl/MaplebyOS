<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

class InventoryImage extends Model
{
    protected $fillable = [
        'image_path',
        'inventory_type',
        'inventory_id',
        'is_primary',
        'sort_order',
    ];

    public function inventory()
    {
        return $this->morphTo();
    }
}
