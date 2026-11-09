<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Money paid to a shipping line so it releases a container. The refund that
 * comes back after the empty is returned is its own record (DepositRefund).
 */
class ContainerDeposit extends Model
{
    use SoftDeletes;

    protected $fillable = ['container_id', 'shipping_line_id', 'amount', 'currency', 'paid_on', 'paid_by', 'payment_reference', 'notes', 'created_by'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date:Y-m-d',
    ];

    public const PAID_BY = [
        'mapleby' => 'Mapleby',
        'client' => 'Client',
    ];

    protected static function booted(): void
    {
        static::created(function (ContainerDeposit $deposit) {
            $deposit->refund()->create(['owner_id' => $deposit->created_by])->syncStatus();
        });
    }

    public function container()
    {
        return $this->belongsTo(Container::class)->withTrashed();
    }

    public function shippingLine()
    {
        return $this->belongsTo(ShippingLine::class);
    }

    public function refund()
    {
        return $this->hasOne(DepositRefund::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** What should come back: the deposit less deductions. */
    public function expectedRefund(): float
    {
        return round(max(0, (float) $this->amount - (float) ($this->refund?->deductions ?? 0)), 2);
    }

    /** Still owed back by the shipping line. Zero once refunded in full or written off. */
    public function outstanding(): float
    {
        $refund = $this->refund;
        if ($refund && $refund->status === DepositRefund::STATUS_WRITTEN_OFF) {
            return 0.0;
        }

        return round(max(0, $this->expectedRefund() - (float) ($refund?->amount_received ?? 0)), 2);
    }
}
