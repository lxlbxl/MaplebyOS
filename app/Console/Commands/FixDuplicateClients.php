<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\Entities\Client;
use Modules\CRM\Entities\Lead;
use Modules\Operations\Entities\Shipment;
use Illuminate\Support\Facades\DB;

class FixDuplicateClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:fix-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicate clients based on case-insensitive name matching';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting duplicate client cleanup...');

        // 1. Normalize Names First (Capitalization)
        $clients = Client::withTrashed()->get(); // Include deleted ones just in case to restore? No, ignore deleted.
        $clients = Client::all();

        $count = 0;
        foreach ($clients as $client) {
            $dirty = false;
            $newName = $client->company_name ? ucwords(strtolower($client->company_name)) : null;
            $newContact = $client->contact_person ? ucwords(strtolower($client->contact_person)) : null;

            if ($newName && $newName !== $client->company_name) {
                $client->company_name = $newName;
                $dirty = true;
            }
            if ($newContact && $newContact !== $client->contact_person) {
                $client->contact_person = $newContact;
                $dirty = true;
            }

            if ($dirty) {
                // We use saveQuietly to avoid triggering updated_at if we want, but normal save is fine.
                // However, we want to bypass mutators just in case, but actually our mutators do the same thing.
                // So plain save() is good.
                $client->save();
                $count++;
            }
        }
        $this->info("Normalized names for $count clients.");

        // 2. Identify Duplicates by Company Name
        $this->mergeByField('company_name');

        // 3. Identify Duplicates by Contact Person (only if company_name is null to avoid aggressive merging)
        // Logic: specific to duplicates where company name is missing.
        // Actually, if company name is same, they are merged. If company name is distinct, they are kept.
        // If company name is null, we check contact_person.
        $this->mergeByField('contact_person', true);

        $this->info('Duplicate cleanup complete.');
    }

    private function mergeByField($field, $onlyIfNullCompany = false)
    {
        // We find exact matches now since we normalized them.
        $query = Client::select($field, DB::raw('count(*) as count'))
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->groupBy($field)
            ->having('count', '>', 1);

        if ($onlyIfNullCompany) {
            $query->whereNull('company_name');
        }

        $groups = $query->get();

        foreach ($groups as $group) {
            $name = $group->$field;
            $this->info("Merging duplicates for '$name' ($field)...");

            $duplicatesQuery = Client::where($field, $name);
            if ($onlyIfNullCompany) {
                $duplicatesQuery->whereNull('company_name');
            }

            $duplicates = $duplicatesQuery->orderBy('id')->get(); // Keep oldest as master

            // Master is the first one (oldest)
            $master = $duplicates->first();
            $slaves = $duplicates->slice(1);

            foreach ($slaves as $slave) {
                // Move relations
                Shipment::where('client_id', $slave->id)->update(['client_id' => $master->id]);
                Lead::where('client_id', $slave->id)->update(['client_id' => $master->id]);

                // Merge data if master is missing info that slave has
                if (empty($master->email) && !empty($slave->email))
                    $master->email = $slave->email;
                if (empty($master->phone) && !empty($slave->phone))
                    $master->phone = $slave->phone;
                if (empty($master->address) && !empty($slave->address))
                    $master->address = $slave->address;

                $master->save();

                // Delete slave
                $slave->delete(); // Soft delete
                $this->info("Merged client ID {$slave->id} into {$master->id}.");
            }
        }
    }
}
