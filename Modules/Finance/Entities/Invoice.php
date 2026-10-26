<?php

namespace Modules\Finance\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Entities\Client;

class Invoice extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft' => 'Draft', 'sent' => 'Sent', 'partial' => 'Partly paid', 'paid' => 'Paid', 'void' => 'Void'];

    protected $fillable = ['invoice_number', 'client_id', 'issue_date', 'due_date', 'status', 'currency', 'total', 'amount_paid', 'notes', 'created_by'];

    protected $casts = [
        'issue_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $appends = ['balance'];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $prefix = 'INV-' . now()->format('ym') . '-';
                $last = static::withTrashed()->where('invoice_number', 'like', $prefix . '%')->max('invoice_number');
                $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
                $invoice->invoice_number = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getBalanceAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    /**
     * Recompute total from its charge lines and paid amount from payments.
     */
    public function recalculate(): void
    {
        $this->total = $this->charges()->sum('amount');
        $this->amount_paid = $this->payments()->sum('amount');
        if ($this->status !== 'void') {
            $this->status = match (true) {
                $this->total > 0 && $this->amount_paid >= $this->total - 0.01 => 'paid',
                $this->amount_paid > 0 => 'partial',
                default => $this->status === 'paid' || $this->status === 'partial' ? 'sent' : $this->status,
            };
        }
        $this->save();

        foreach ($this->charges()->with('shipment')->get()->pluck('shipment')->filter()->unique('id') as $shipment) {
            $invoices = static::whereIn('id', $shipment->charges()->whereNotNull('invoice_id')->pluck('invoice_id'))->where('status', '!=', 'void')->get();
            $status = $invoices->isEmpty() ? $shipment->payment_status
                : ($invoices->every(fn ($i) => $i->status === 'paid') ? 'paid' : ($invoices->sum('amount_paid') > 0 ? 'partial' : 'unpaid'));
            if ($status !== $shipment->payment_status) {
                $shipment->update(['payment_status' => $status]);
            }
        }
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function charges()
    {
        return $this->hasMany(ShipmentCharge::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
