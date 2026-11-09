<?php

namespace Modules\Operations\Services;

use App\Models\User;
use Modules\Operations\Entities\ContainerDeposit;

/**
 * Writes every change to a container deposit or its refund to the activity
 * log ('deposits'), with the old and new values, so the CEO and Head of
 * Business can see who recorded, chased, confirmed or wrote off what.
 */
class DepositLedger
{
    public const LOG = 'deposits';

    /** Fields worth showing in the trail, with readable names. */
    public const FIELD_LABELS = [
        'amount' => 'Deposit',
        'currency' => 'Currency',
        'paid_on' => 'Paid on',
        'paid_by' => 'Paid by',
        'payment_reference' => 'Payment reference',
        'shipping_line_id' => 'Shipping line',
        'status' => 'Refund status',
        'owner_id' => 'Followed up by',
        'claim_submitted_on' => 'Claim filed',
        'claim_reference' => 'Claim reference',
        'deductions' => 'Deductions',
        'deduction_reason' => 'Deduction reason',
        'amount_received' => 'Refund received',
        'received_on' => 'Received on',
        'received_reference' => 'Refund reference',
        'next_follow_up_on' => 'Next follow-up',
        'empty_returned_on' => 'Empty returned',
        'notes' => 'Notes',
    ];

    public static function log(ContainerDeposit $deposit, string $event, string $description, ?User $by, array $old = [], array $new = [], array $extra = []): void
    {
        $changes = [];
        foreach ($new as $field => $value) {
            $before = $old[$field] ?? null;
            if ((string) $before === (string) $value) {
                continue;
            }
            $changes[$field] = ['label' => self::FIELD_LABELS[$field] ?? $field, 'old' => $before, 'new' => $value];
        }
        if ($event === 'updated' && !$changes && !$extra) {
            return;
        }

        activity(self::LOG)
            ->performedOn($deposit)
            ->causedBy($by)
            ->event($event)
            ->withProperties(array_filter([
                'container' => $deposit->container?->container_number,
                'changes' => $changes ?: null,
            ] + $extra, fn ($v) => $v !== null))
            ->log($description);
    }
}
