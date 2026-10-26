<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'make',
        'model',
        'year',
        'serial_number',
        'condition',
        'status',
        'purchase_price',
        'sale_price',
        'purchase_date',
        'buyer_id',
        'supplier_id',
        'description',
    ];

    public function images()
    {
        return $this->morphMany(InventoryImage::class, 'inventory');
    }

    public function logs()
    {
        return $this->morphMany(YardLog::class, 'inventory');
    }
}
