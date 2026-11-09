<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The refund of a container deposit, tracked apart from the deposit itself.
 *
 * Most statuses follow from the facts (empty returned, claim filed, money
 * received). Confirmed, disputed and written off are set by the CEO or Head
 * of Business and stay until they change them.
 */
class DepositRefund extends Model
{
    protected $fillable = [
        'container_deposit_id', 'status', 'owner_id', 'claim_submitted_on', 'claim_reference', 'deductions', 'deduction_reason',
        'amount_received', 'received_on', 'received_reference', 'next_follow_up_on', 'last_followed_up_on',
        'verified_by', 'verified_at', 'notes',
    ];

    protected $casts = [
        'claim_submitted_on' => 'date:Y-m-d',
        'received_on' => 'date:Y-m-d',
        'next_follow_up_on' => 'date:Y-m-d',
        'last_followed_up_on' => 'date:Y-m-d',
        'verified_at' => 'datetime',
        'deductions' => 'decimal:2',
        'amount_received' => 'decimal:2',
    ];

    protected $appends = ['status_label'];

    const STATUS_AWAITING_EMPTY = 'awaiting_empty';
    const STATUS_TO_CLAIM = 'to_claim';
    const STATUS_CLAIMED = 'claimed';
    const STATUS_PART_RECEIVED = 'part_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DISPUTED = 'disputed';
    const STATUS_WRITTEN_OFF = 'written_off';

    public const STATUSES = [
        self::STATUS_AWAITING_EMPTY => 'Empty not returned yet',
        self::STATUS_TO_CLAIM => 'Empty returned, claim not filed',
        self::STATUS_CLAIMED => 'Claim filed, awaiting refund',
        self::STATUS_PART_RECEIVED => 'Part refunded',
        self::STATUS_RECEIVED => 'Refunded, awaiting confirmation',
        self::STATUS_VERIFIED => 'Refund confirmed',
        self::STATUS_DISPUTED => 'Disputed',
        self::STATUS_WRITTEN_OFF => 'Written off',
    ];

    /** Set by oversight; not recalculated from the facts. */
    public const MANUAL_STATUSES = [self::STATUS_VERIFIED, self::STATUS_DISPUTED, self::STATUS_WRITTEN_OFF];

    /** Nothing more to chase. */
    public const CLOSED_STATUSES = [self::STATUS_VERIFIED, self::STATUS_WRITTEN_OFF];

    /** How long after filing a claim to chase it if nobody set a date. */
    public const DEFAULT_CHASE_DAYS = 14;

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function deposit()
    {
        return $this->belongsTo(ContainerDeposit::class, 'container_deposit_id')->withTrashed();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function followUps()
    {
        return $this->hasMany(DepositFollowUp::class)->latest('contacted_on')->latest('id');
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', self::CLOSED_STATUSES);
    }

    public function scopeFollowUpDue($query)
    {
        return $query->open()->where('status', '!=', self::STATUS_AWAITING_EMPTY)
            ->whereNotNull('next_follow_up_on')->whereDate('next_follow_up_on', '<=', today());
    }

    /** The status the facts point to, ignoring statuses oversight has set. */
    public function derivedStatus(): string
    {
        $deposit = $this->deposit;
        $expected = $deposit ? $deposit->expectedRefund() : 0;
        $received = (float) $this->amount_received;

        if ($received > 0 && $received + 0.005 >= $expected) {
            return self::STATUS_RECEIVED;
        }
        if ($received > 0) {
            return self::STATUS_PART_RECEIVED;
        }
        if ($this->claim_submitted_on) {
            return self::STATUS_CLAIMED;
        }
        if ($deposit?->container?->empty_returned_on) {
            return self::STATUS_TO_CLAIM;
        }

        return self::STATUS_AWAITING_EMPTY;
    }

    /**
     * Bring the status in line with the facts and make sure an open refund
     * that can be chased has a next follow-up date.
     */
    public function syncStatus(): self
    {
        $this->loadMissing('deposit.container');
        if (!in_array($this->status, self::MANUAL_STATUSES, true)) {
            $this->status = $this->derivedStatus();
        }

        if (!$this->next_follow_up_on && !$this->isClosed()) {
            if ($this->status === self::STATUS_TO_CLAIM) {
                $this->next_follow_up_on = Carbon::today();
            } elseif (in_array($this->status, [self::STATUS_CLAIMED, self::STATUS_PART_RECEIVED], true)) {
                $this->next_follow_up_on = Carbon::parse($this->claim_submitted_on ?? today())->addDays(self::DEFAULT_CHASE_DAYS);
            }
        }
        if ($this->isClosed() || $this->status === self::STATUS_RECEIVED) {
            $this->next_follow_up_on = null;
        }

        if ($this->isDirty()) {
            $this->save();
        }

        return $this;
    }

    /** Re-check every refund on these containers (after their empty return date changed). */
    public static function syncForContainers(array $containerIds): void
    {
        if (!$containerIds) {
            return;
        }
        static::whereHas('deposit', fn ($d) => $d->whereIn('container_id', $containerIds))
            ->get()->each(fn (DepositRefund $r) => $r->unsetRelation('deposit')->syncStatus());
    }
}
