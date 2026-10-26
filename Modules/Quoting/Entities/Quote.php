<?php

namespace Modules\Quoting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_number',
        'client_id',
        'lead_id',
        'status',
        'issue_date',
        'expiry_date',
        'subtotal',
        'tax_total',
        'total_amount',
        'notes',
        'terms_conditions',
        'shipment_id',
        'inventory_type',
        'inventory_id',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(\Modules\CRM\Entities\Client::class);
    }

    public function lead()
    {
        return $this->belongsTo(\Modules\CRM\Entities\Lead::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function shipment()
    {
        return $this->belongsTo(\Modules\Operations\Entities\Shipment::class);
    }

    public function inventory()
    {
        return $this->morphTo();
    }

    public function payments()
    {
        return $this->hasMany(\Modules\Finance\Entities\Payment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getPaymentStatusAttribute()
    {
        $paid = $this->paid_amount;
        $total = $this->total_amount;

        if ($paid >= $total && $total > 0) {
            return 'paid';
        } elseif ($paid > 0) {
            return 'partial';
        }
        return 'pending';
    }

    public function generatePdf()
    {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('quoting::pdf.quote', ['quote' => $this]);
    }
}
