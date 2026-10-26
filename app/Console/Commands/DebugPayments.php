<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Finance\Entities\Payment;
use Modules\Quoting\Entities\Quote;
use Modules\Operations\Entities\Shipment;

class DebugPayments extends Command
{
    protected $signature = 'app:debug-payments';
    protected $description = 'Debug and fix payment status synchronization';

    public function handle()
    {
        $this->info('Starting Payment Debugger...');

        // 1. Check Payments -> Quotes -> Shipments Linkage
        $payments = Payment::with('quote')->get();
        $this->info("Found {$payments->count()} total payments.");

        $orphanedDetails = [];

        foreach ($payments as $payment) {
            if (!$payment->quote) {
                $this->error("Payment {$payment->id} (Amount: {$payment->amount}) has NO linked Quote!");
                continue;
            }

            $quote = $payment->quote;
            if (!$quote->shipment_id) {
                $orphanedDetails[] = [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'quote_number' => $quote->quote_number,
                    'quote_id' => $quote->id,
                ];
            }
        }

        if (count($orphanedDetails) > 0) {
            $this->warn("Found " . count($orphanedDetails) . " payments linked to Quotes WITHOUT Shipments:");
            $this->table(['Payment ID', 'Amount', 'Quote #', 'Quote ID'], $orphanedDetails);
            $this->warn("NOTE: These payments will NOT show up on any Shipment dashboard until the Quote is linked to a Shipment.");
        } else {
            $this->info("All payments are linked to Quotes which are linked to Shipments.");
        }

        // 2. Recalculate Shipment Statuses
        $this->info("\nrecalculating Shipment statuses...");
        $shipments = Shipment::with('quotes.payments')->get();

        foreach ($shipments as $shipment) {
            $totalQuoted = $shipment->quotes->sum('total_amount');
            $totalPaid = $shipment->quotes->sum(function ($q) {
                return $q->payments->sum('amount');
            });

            $calculatedStatus = 'unpaid';
            if ($totalQuoted > 0) {
                if ($totalPaid >= ($totalQuoted - 0.01)) {
                    $calculatedStatus = 'paid';
                } elseif ($totalPaid > 0) {
                    $calculatedStatus = 'partial';
                }
            }

            if ($shipment->payment_status !== $calculatedStatus) {
                $this->info("Updating Shipment {$shipment->shipment_number}: {$shipment->payment_status} -> {$calculatedStatus} (Paid: {$totalPaid})");
                $shipment->update(['payment_status' => $calculatedStatus]);
            }
        }

        $this->info("Payment sync complete.");
    }
}
