<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** One chase of the shipping line about a refund: who, when, how and what they said. */
class DepositFollowUp extends Model
{
    protected $fillable = ['deposit_refund_id', 'user_id', 'contacted_on', 'channel', 'contact_person', 'outcome', 'next_follow_up_on'];

    protected $casts = [
        'contacted_on' => 'date:Y-m-d',
        'next_follow_up_on' => 'date:Y-m-d',
    ];

    public const CHANNELS = [
        'call' => 'Phone call',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'visit' => 'Office visit',
    ];

    public function refund()
    {
        return $this->belongsTo(DepositRefund::class, 'deposit_refund_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
