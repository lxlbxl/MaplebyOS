<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Modules\CRM\Entities\Client;
use Modules\CRM\Entities\Lead;
use Faker\Factory as Faker;

class AnonymizeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:anonymize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymize sensitive data (names, emails, phones) for demo purposes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create();
        $this->info('Starting anonymization process...');

        // 1. Anonymize Users
        $this->info('Anonymizing Users...');
        $users = User::all();
        foreach ($users as $user) {
            // Optional: Skip specific users if needed, e.g., keep 'Admin' but change name
            // For now, we anonymize everyone.
            $user->name = $faker->name;
            // Keep email structure but make it fake, or keep it if it's the login?
            // User request: "Change all the Names... so I can present it without showing actual client data"
            // Usually presentation needs login. I'll mostly touch Client/Lead data, but user said "Change all the Names".
            // I will update User names but maybe NOT emails to avoid locking them out if they don't know the new email.
            // Wait, "Change all the Names". I'll stick to Names for Users.
            // But for Clients, I should anonymize everything as they are "client data".
            $user->save();
        }
        $this->info(count($users) . ' users anonymized (Names only).');

        // 2. Anonymize Clients
        $this->info('Anonymizing Clients...');
        $clients = Client::all();
        foreach ($clients as $client) {
            $client->company_name = $faker->company;
            $client->contact_person = $faker->name;
            $client->email = $faker->unique()->safeEmail;
            $client->phone = $faker->phoneNumber;
            if ($client->secondary_phone) {
                $client->secondary_phone = $faker->phoneNumber;
            }
            $client->address = $faker->streetAddress;
            $client->city = $faker->city;
            // Keep state/country/zip somewhat realistic or just fake it? optimizing for "privacy". Fake it.
            $client->state_province = $faker->state;
            $client->postal_code = $faker->postcode;

            $client->save();
        }
        $this->info(count($clients) . ' clients anonymized.');

        // 3. Anonymize Leads
        $this->info('Anonymizing Leads...');
        $leads = Lead::all();
        foreach ($leads as $lead) {
            // Title might contain PII, so we generate a generic title
            $lead->title = $faker->bs; // "integrate vertical bandwidth"
            if ($lead->contact_name) {
                $lead->contact_name = $faker->name;
            }
            if ($lead->contact_email) {
                $lead->contact_email = $faker->safeEmail;
            }
            if ($lead->contact_phone) {
                $lead->contact_phone = $faker->phoneNumber;
            }
            $lead->save();
        }
        $this->info(count($leads) . ' leads anonymized.');

        $this->info('Anonymization complete!');
    }
}
