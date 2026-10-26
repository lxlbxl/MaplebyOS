<?php

namespace Modules\Finance\Services;

use Modules\Finance\Entities\Payment;
use Modules\Quoting\Entities\Quote;
use Modules\Operations\Entities\Shipment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Recalculate and update the payment status for a specific Quote.
     */
    public function syncQuoteStatus(Quote $quote)
    {
        $totalAmount = $quote->total_amount;
        $paidAmount = $quote->payments()->sum('amount');

        $status = 'pending'; // Default for quote payment status (different from approval status)

        // Note: Quote model has 'status' for approval (draft, sent, approved).
        // We might want to add a specific 'payment_status' column to quotes later if not present,
        // but for now we rely on the dynamic accessor or just calculating it here for the parent shipment.

        // This method primarily serves to trigger the Shipment sync.
        if ($quote->shipment_id) {
            $this->syncShipmentStatus($quote->shipment_id);
        }

        return [
            'total' => $totalAmount,
            'paid' => $paidAmount,
        ];
    }

    /**
     * Recalculate and update the payment status for a specific Shipment.
     */
    public function syncShipmentStatus($shipmentId)
    {
        $shipment = Shipment::with('quotes.payments')->find($shipmentId);

        if (!$shipment) {
            return;
        }

        $totalQuoted = $shipment->quotes->sum('total_amount');
        $totalPaid = $shipment->quotes->sum(function ($quote) {
            return $quote->payments->sum('amount');
        });

        $status = 'unpaid';
        if ($totalQuoted > 0) {
            if ($totalPaid >= ($totalQuoted - 0.01)) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            }
        }

        if ($shipment->payment_status !== $status) {
            Log::info("PaymentService: Updating Shipment {$shipment->id} status: {$shipment->payment_status} -> {$status}");
            $shipment->update(['payment_status' => $status]);
        }
    }
}
