<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Quoting\Entities\Quote;
use Modules\Operations\Entities\Shipment;
use Modules\Finance\Services\PaymentService;

class LinkQuote extends Command
{
    protected $signature = 'app:link-quote {quote_number} {shipment_id}';
    protected $description = 'Link a quote to a shipment and sync status';

    public function handle(PaymentService $paymentService)
    {
        $quoteNumber = $this->argument('quote_number');
        $shipmentId = $this->argument('shipment_id');

        $quote = Quote::where('quote_number', $quoteNumber)->first();
        if (!$quote) {
            $this->error("Quote $quoteNumber not found.");
            return;
        }

        $shipment = Shipment::find($shipmentId);
        if (!$shipment) {
            $this->error("Shipment $shipmentId not found.");
            return;
        }

        $quote->update(['shipment_id' => $shipment->id]);
        $this->info("Linked Quote {$quote->id} to Shipment {$shipment->id}.");

        $paymentService->syncQuoteStatus($quote);
        $this->info("Synced Payment Status.");

        //Verify
        $shipment->refresh();
        $this->info("New Shipment Payment Status: " . $shipment->payment_status);

        $paid = $quote->payments()->sum('amount');
        $this->info("Quote Paid Amount: " . $paid);
    }
}
