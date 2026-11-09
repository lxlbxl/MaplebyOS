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

    /** Customs clearing of a client's general cargo in Nigeria (usually in NGN). */
    public const CLEARING_CATEGORIES = [
        'cost' => ['Customs duty', 'VAT & levies', 'Terminal charges', 'Shipping line charges', 'Examination', 'Demurrage', 'Storage', 'Haulage', 'Documentation', 'Container deposit', 'Other'],
        'charge' => ['Agency fee', 'Duty & taxes', 'Terminal & shipping line charges', 'Haulage', 'Demurrage', 'Storage', 'Documentation', 'Other'],
    ];

    /** Boxes / lite cargo riding in a car container. */
    public const BOX_CATEGORIES = [
        'cost' => ['Handling', 'Packing', 'Clearing', 'Delivery', 'Other'],
        'charge' => ['Shipping', 'Handling', 'Clearing', 'Delivery', 'Service fee', 'Other'],
    ];

    /** Picklist for a shipment's cargo type. */
    public static function categoriesFor(?string $cargoType): array
    {
        return match ($cargoType) {
            Shipment::CARGO_GENERAL => self::CLEARING_CATEGORIES,
            Shipment::CARGO_BOX => self::BOX_CATEGORIES,
            default => self::CATEGORIES,
        };
    }

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
