<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use League\Csv\Reader;
use Modules\CRM\Entities\Client;
use Modules\Operations\Entities\Shipment;
use Illuminate\Support\Carbon;

class ImportLegacyShipments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-legacy-shipments {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy shipments from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        $this->info('Starting import...');

        $bar = $this->output->createProgressBar(count($csv));
        $bar->start();

        foreach ($records as $record) {
            $this->processRow($record);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Import completed.');

        return 0;
    }

    private function processRow(array $row)
    {
        // 1. Find or Create Client
        $clientName = trim($row['NAME'] ?? '');
        if (empty($clientName)) {
            return; // Skip empty rows
        }

        $client = Client::firstOrCreate(
            ['contact_person' => $clientName],
            [
                'phone' => $row['PHONE NUMBER'] ?? null,
                'source' => 'legacy_import',
            ]
        );

        // 2. Parse Vehicle Info
        $carDetails = trim($row['CAR MAKE & MODEL'] ?? '');
        $vehicleYear = null;
        $vehicleMake = null;
        $vehicleModel = null;

        if (!empty($carDetails)) {
            $parts = explode(' ', $carDetails);
            if (is_numeric($parts[0])) {
                $vehicleYear = (int) array_shift($parts);
            }
            if (!empty($parts)) {
                $vehicleMake = array_shift($parts);
            }
            if (!empty($parts)) {
                $vehicleModel = implode(' ', $parts);
            }
        }

        // 3. Map Shipment Data
        $dateTimestamp = $row['Timestamp'] ?? null;
        $dateReceived = $row['Column 1'] ?? null;

        if ($dateReceived && $dateReceived !== '1/5/2026') {
            // Basic date parsing if needed, but Carbon usually handles '1/5/2026'
        }

        Shipment::create([
            'client_id' => $client->id,
            'title' => $carDetails ?: 'Untitled Shipment',
            'vin' => $row['VIN # '] ?? null, // Note the space in CSV header if present, adjusting based on CSV dump
            'vehicle_year' => $vehicleYear,
            'vehicle_make' => $vehicleMake,
            'vehicle_model' => $vehicleModel,
            'vehicle_color' => $row['COLOUR'] ?? null,
            'service_type' => $row['MODE OF SHIPPING'] ?? 'unknown',
            'status' => $this->mapStatus($row['READY TO GO?'] ?? ''),
            'tow_required' => strtoupper(trim($row['TOW TRUCK?'] ?? '')) === 'YES',
            'driver_name' => $row['NAME OF DRIVER'] ?? null,
            'ownership_documents' => $this->mapOwnership($row['OWNERSHIP'] ?? ''),
            'location_notes' => $row['LOCATION'] ?? null,
            'description' => $row['EXTRA NOTES(IF ANY)'] . ' ' . ($row['IF NO, WHY?'] ?? ''),
            'created_at' => $dateTimestamp ? Carbon::parse($dateTimestamp) : now(),
            'date_received' => $dateReceived ? Carbon::parse($dateReceived) : null,
        ]);
    }

    private function mapStatus($val)
    {
        $val = strtoupper(trim($val));
        if ($val === 'YES')
            return 'pending'; // Ready to go -> Pending shipment
        return 'draft'; // Not ready -> Draft or whatever default
    }

    private function mapOwnership($val)
    {
        $val = strtoupper(trim($val));
        return in_array($val, ['YES', 'BILL OF SALES', 'FILE']);
    }
}
