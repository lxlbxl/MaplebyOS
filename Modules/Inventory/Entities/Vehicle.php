<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vehicle extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200);
    }

    protected $fillable = [
        'make',
        'model',
        'year',
        'vin',
        'color',
        'condition',
        'status',
        'purchase_price',
        'sale_price',
        'purchase_date',
        'sale_date',
        'buyer_id',
        'supplier_id',
        'notes',
    ];

    public function images()
    {
        return $this->morphMany(InventoryImage::class, 'inventory');
    }

    public function logs()
    {
        return $this->morphMany(YardLog::class, 'inventory');
    }

    public function buyer()
    {
        return $this->belongsTo(\Modules\CRM\Entities\Client::class, 'buyer_id');
    }

    public function supplier()
    {
        return $this->belongsTo(\Modules\CRM\Entities\Client::class, 'supplier_id');
    }
}
