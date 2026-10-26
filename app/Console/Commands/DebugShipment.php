<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Operations\Entities\Shipment;
use Modules\Quoting\Entities\Quote;

class DebugShipment extends Command
{
    protected $signature = 'app:debug-shipment {number}';
    protected $description = 'Debug shipment data linkage';

    public function handle()
    {
        $number = $this->argument('number');
        $this->info("Inspecting Shipment: $number");

        $shipment = Shipment::where('shipment_number', $number)->with('client', 'quotes.payments')->first();

        if (!$shipment) {
            $this->error("Shipment not found!");
            return;
        }

        $this->line("ID: " . $shipment->id);
        $this->line("Client: " . ($shipment->client->company_name ?? 'Unknown'));
        $this->line("Payment Status (DB): " . $shipment->payment_status);

        $this->info("Linked Quotes (" . $shipment->quotes->count() . "):");
        foreach ($shipment->quotes as $quote) {
            $paid = $quote->payments->sum('amount');
            $this->line("- Quote: {$quote->quote_number} | Total: {$quote->total_amount} | Paid: {$paid} | Status: {$quote->payment_status}");
        }

        if ($shipment->quotes->count() === 0) {
            $this->warn("No quotes linked to this shipment!");
            $this->info("Checking for orphan quotes for Client ID {$shipment->client_id}...");

            $orphans = Quote::where('client_id', $shipment->client_id)->get();
            foreach ($orphans as $orphan) {
                $linkedShipment = $orphan->shipment_id ? "Linked to #{$orphan->shipment_id}" : "UNLINKED";
                $this->line("- Orphan: {$orphan->quote_number} | {$linkedShipment} | Total: {$orphan->total_amount}");
            }
        }
    }
}
