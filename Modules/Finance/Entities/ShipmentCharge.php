<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Operations\Entities\Shipment;

class ShipmentCharge extends Model
{
    public const KIND_COST = 'cost';
    public const KIND_CHARGE = 'charge';

    /** Picklists so categories stay consistent across cars. */
    public const CATEGORIES = [
        'cost' => ['Auction fees', 'Storage', 'Towing', 'Loading', 'Ocean freight', 'Documentation', 'Clearing', 'Port charges', 'Delivery', 'Other'],
        'charge' => ['Vehicle price', 'Shipping', 'Clearing', 'Delivery', 'Service fee', 'Other'],
    ];

    protected $fillable = ['shipment_id', 'kind', 'category', 'description', 'amount', 'currency', 'incurred_on', 'invoice_id', 'created_by'];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_on' => 'date:Y-m-d',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
