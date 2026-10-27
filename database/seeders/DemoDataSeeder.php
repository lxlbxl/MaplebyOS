<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Communications\Entities\Communication;
use Modules\CRM\Entities\Client;
use Modules\CRM\Entities\Lead;
use Modules\Finance\Entities\Invoice;
use Modules\Finance\Entities\Payment;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Inventory\Entities\Equipment;
use Modules\Inventory\Entities\Vehicle;
use Modules\Inventory\Entities\YardLog;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShipmentMilestone;
use Modules\Operations\Services\ContainerNumber;
use Modules\Quoting\Entities\Quote;
use Modules\Quoting\Entities\QuoteItem;
use Modules\Quoting\Entities\QuoteTemplate;
use Modules\Tasks\Entities\Task;

/**
 * Fills every module with realistic demo data for testing.
 *
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Refuses to run when clients already exist, so it never mixes with live data.
 * Every demo email uses the reserved example.com domain.
 */
class DemoDataSeeder extends Seeder
{
    private Carbon $today;
    private array $staff = [];

    public function run(): void
    {
        if (Client::withTrashed()->exists()) {
            $this->command?->warn('Clients already exist; demo data not seeded.');
            return;
        }

        mt_srand(2026);
        $this->today = Carbon::today();

        Model::unguarded(function () {
            DB::transaction(function () {
                $this->seedStaff();
                auth()->setUser($this->staff['admin']);

                $clients = $this->seedClients();
                $leads = $this->seedLeads($clients);
                $this->seedCommunications($clients, $leads);
                $containers = $this->seedContainers();
                $shipments = $this->seedShipments($clients, $containers);
                $this->seedFinance($shipments);
                $this->seedQuotes($clients, $leads, $shipments);
                $this->seedInventory($clients);
                $this->seedTasks($clients, $leads, $shipments);
                $this->seedNotifications();
            });
        });

        $this->command?->info('Demo data seeded.');
    }

    // ---------------------------------------------------------------- helpers

    private function pick(array $items)
    {
        return $items[array_rand($items)];
    }

    private function money(int $min, int $max, int $step = 5): float
    {
        return (float) (intdiv(mt_rand($min, $max), $step) * $step);
    }

    private function backdate(Model $model, Carbon $at): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
        $model->timestamps = true;
    }

    private function vin(): string
    {
        $chars = 'ABCDEFGHJKLMNPRSTUVWXYZ0123456789';
        $vin = '';
        for ($i = 0; $i < 17; $i++) {
            $vin .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $vin;
    }

    private function containerNumber(string $owner): string
    {
        do {
            $first10 = $owner . mt_rand(100000, 999999);
        } while (ContainerNumber::checkDigit($first10) === 10);
        return $first10 . ContainerNumber::checkDigit($first10);
    }

    private function phoneNg(): string
    {
        return '+234 ' . $this->pick(['803', '805', '806', '808', '809', '810', '813', '816', '903']) . ' ' . mt_rand(100, 999) . ' ' . mt_rand(1000, 9999);
    }

    private function phoneCa(): string
    {
        return '+1 ' . $this->pick(['416', '647', '905', '514', '613', '604']) . ' ' . mt_rand(200, 999) . ' ' . mt_rand(1000, 9999);
    }

    // ---------------------------------------------------------------- staff

    private function seedStaff(): void
    {
        $this->staff['admin'] = User::where('email', 'admin@maplebyinc.com')->firstOrFail();
        $this->staff['staff'] = User::where('email', 'staff@maplebyinc.com')->first() ?? $this->staff['admin'];

        $people = [
            'sales' => ['Chiamaka Obi', 'sales'],
            'ops' => ['Daniel Tremblay', 'operations'],
            'ops2' => ['Ibrahim Musa', 'operations'],
            'finance' => ['Grace Adeyemi', 'finance'],
        ];
        foreach ($people as $key => [$name, $role]) {
            $user = User::create([
                'name' => $name,
                'email' => Str::slug($name, '.') . '@example.com',
                'role' => $role,
                'is_active' => true,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('Staff');
            }
            $this->staff[$key] = $user;
        }
    }

    private function anyStaff(): User
    {
        return $this->pick(array_values($this->staff));
    }

    // ---------------------------------------------------------------- CRM

    private function seedClients(): array
    {
        $buyers = [
            ['Okafor Motors Ltd', 'Emeka Okafor', 'Lagos', 'Lagos'],
            ['Adebayo Autos', 'Tunde Adebayo', 'Ikeja', 'Lagos'],
            [null, 'Ngozi Eze', 'Enugu', 'Enugu'],
            ['Balogun Car Mart', 'Femi Balogun', 'Abeokuta', 'Ogun'],
            [null, 'Aisha Bello', 'Abuja', 'FCT'],
            ['Delta Prime Vehicles', 'Ovie Edafe', 'Warri', 'Delta'],
            [null, 'Chinedu Nwosu', 'Port Harcourt', 'Rivers'],
            ['Kano Auto Hub', 'Sani Abdullahi', 'Kano', 'Kano'],
            [null, 'Funmilayo Ogunleye', 'Ibadan', 'Oyo'],
            ['Lekki Luxury Rides', 'Kelechi Uche', 'Lekki', 'Lagos'],
            [null, 'Yetunde Afolabi', 'Lagos', 'Lagos'],
            ['Benin Motors Co', 'Osagie Igbinedion', 'Benin City', 'Edo'],
            [null, 'Ifeanyi Okonkwo', 'Onitsha', 'Anambra'],
            ['Apapa Fleet Services', 'Bolaji Salami', 'Apapa', 'Lagos'],
            [null, 'Hauwa Garba', 'Kaduna', 'Kaduna'],
            ['Cotonou Car Traders', 'Koffi Mensah', 'Cotonou', 'Littoral'],
            [null, 'Segun Martins', 'Lagos', 'Lagos'],
            ['Uyo Auto Plaza', 'Imaobong Etim', 'Uyo', 'Akwa Ibom'],
        ];
        $suppliers = [
            ['Maple Leaf Auto Sales', 'Marc Gagnon', 'Toronto', 'Ontario', 'Canada'],
            ['Great Lakes Used Cars', 'Sarah Mitchell', 'Mississauga', 'Ontario', 'Canada'],
            ['Laval Auto Export', 'Julien Roy', 'Laval', 'Quebec', 'Canada'],
            ['Pacific Rim Motors', 'Kevin Chen', 'Vancouver', 'British Columbia', 'Canada'],
            ['Jersey Auto Brokers', 'Michael Russo', 'Newark', 'New Jersey', 'USA'],
            ['Lone Star Salvage', 'Brandon Hayes', 'Houston', 'Texas', 'USA'],
        ];
        $both = [
            ['Diaspora Drive Inc', 'Olumide Coker', 'Brampton', 'Ontario', 'Canada'],
            ['Naija-Canada Traders', 'Adaeze Okeke', 'Calgary', 'Alberta', 'Canada'],
        ];

        $clients = [];
        $make = function (string $type, ?string $company, string $person, string $city, string $state, string $country, string $phone) use (&$clients) {
            $n = count($clients);
            $client = Client::create([
                'type' => $type,
                'company_name' => $company,
                'contact_person' => $person,
                'email' => Str::slug($company ?? $person, '.') . '@example.com',
                'phone' => $phone,
                'secondary_phone' => mt_rand(0, 2) === 0 ? $phone . '1' : null,
                'address' => mt_rand(1, 240) . ' ' . $this->pick(['Admiralty Way', 'Allen Avenue', 'Awolowo Road', 'Queen Street', 'King Street W', 'Rue Sainte-Catherine', 'Main Street', 'Broad Street']),
                'city' => $city,
                'state_province' => $state,
                'country' => $country,
                'postal_code' => $country === 'Canada' ? strtoupper(Str::random(3)) . ' ' . mt_rand(1, 9) . 'A' . mt_rand(1, 9) : (string) mt_rand(100001, 999999),
                'tax_id' => $company ? 'TIN-' . mt_rand(10000000, 99999999) : null,
                'notes' => $this->pick([null, 'Prefers WhatsApp updates.', 'Repeat customer — priority loading.', 'Pays by bank transfer in USD.', 'Referred by an existing client.']),
                'source' => $this->pick(['website', 'referral', 'direct', 'whatsapp']),
                'is_active' => mt_rand(0, 12) > 0,
                'created_by' => $this->anyStaff()->id,
            ]);
            $this->backdate($client, $this->today->copy()->subDays(380 - $n * 11));
            $clients[] = $client;
        };

        foreach ($buyers as [$co, $p, $city, $st]) {
            $make('buyer', $co, $p, $city, $st, $city === 'Cotonou' ? 'Benin' : 'Nigeria', $this->phoneNg());
        }
        foreach ($suppliers as [$co, $p, $city, $st, $country]) {
            $make('supplier', $co, $p, $city, $st, $country, $this->phoneCa());
        }
        foreach ($both as [$co, $p, $city, $st, $country]) {
            $make('both', $co, $p, $city, $st, $country, $this->phoneCa());
        }

        return $clients;
    }

    private function buyers(array $clients): array
    {
        return array_values(array_filter($clients, fn ($c) => $c->type !== 'supplier'));
    }

    private function seedLeads(array $clients): array
    {
        $titles = [
            ['2019 Toyota Highlander to Lagos', 'vehicle_export', 9500],
            ['Two Lexus RX 350 — container share', 'vehicle_export', 21000],
            ['Honda Accord 2018, Copart purchase + shipping', 'vehicle_export', 8200],
            ['Fleet of 6 Toyota Hiace buses', 'vehicle_export', 68000],
            ['Caterpillar 320 excavator export', 'equipment_sale', 74000],
            ['Ford F-150 RoRo to Cotonou', 'vehicle_export', 7600],
            ['Mercedes GLE clearing at Tin Can', 'inbound_clearing', 4200],
            ['Full 40HC consolidation for dealership', 'freight_forwarding', 32000],
            ['Generator set (100 kVA) shipment', 'freight_forwarding', 15500],
            ['Toyota Camry 2020 — auction sourcing', 'vehicle_export', 11800],
            ['Range Rover Sport, private seller pickup', 'vehicle_export', 26000],
            ['Kia Sorento + Hyundai Santa Fe', 'vehicle_export', 17400],
            ['Forklift x2 for Apapa warehouse', 'equipment_sale', 23000],
            ['Lexus GX 460 to Abuja', 'vehicle_export', 14200],
            ['Spare parts LCL from Toronto', 'freight_forwarding', 3800],
            ['Clearing for 3 vehicles at Apapa', 'inbound_clearing', 9900],
            ['Toyota Sienna 2017 family van', 'vehicle_export', 9100],
            ['Acura MDX auction bid support', 'vehicle_export', 10400],
            ['Bobcat skid steer export', 'equipment_sale', 28500],
            ['Honda Pilot, keys missing, tow needed', 'vehicle_export', 8700],
            ['Nissan Pathfinder RoRo', 'vehicle_export', 7900],
            ['Monthly consolidation contract', 'freight_forwarding', 45000],
            ['Mercedes Sprinter van conversion', 'vehicle_export', 19800],
            ['Toyota Corolla x4 for ride-hailing fleet', 'vehicle_export', 31000],
        ];
        $statuses = ['new', 'new', 'new', 'contacted', 'contacted', 'contacted', 'qualified', 'qualified', 'proposal', 'proposal', 'negotiation', 'negotiation', 'won', 'won', 'won', 'lost', 'lost'];
        $buyers = $this->buyers($clients);
        $leads = [];

        foreach ($titles as $i => [$title, $service, $value]) {
            $status = $statuses[$i % count($statuses)];
            $created = $this->today->copy()->subDays(mt_rand(2, 200))->setTime(mt_rand(8, 18), mt_rand(0, 59));
            $client = mt_rand(0, 3) > 0 ? $this->pick($buyers) : null;
            $name = $client?->contact_person ?? $this->pick(['Victor Ade', 'Blessing Okoro', 'Musa Danjuma', 'Rita Eze', 'Paul Onyeka']);
            $source = $this->pick(['website', 'referral', 'direct']);

            $lead = Lead::create([
                'client_id' => $client?->id,
                'source' => $source,
                'service_type' => $service,
                'status' => $status,
                'priority' => $this->pick(['low', 'medium', 'medium', 'high', 'urgent']),
                'title' => $title,
                'description' => "Customer enquiry: {$title}. Wants an all-in quote including clearing and delivery to " . ($client?->city ?? 'Lagos') . '.',
                'estimated_value' => $value,
                'contact_name' => $name,
                'contact_email' => $client?->email ?? Str::slug($name, '.') . '@example.com',
                'contact_phone' => $client?->phone ?? $this->phoneNg(),
                'website_form_data' => $source === 'website' ? [
                    'mode_of_shipping' => $this->pick(['MAPLEBY', 'CONTAINER', 'RORO', 'UNSURE']),
                    'origin' => $this->pick(['Toronto', 'Montreal', 'Halifax']),
                    'destination' => $this->pick(['Lagos', 'Abuja', 'Port Harcourt']),
                    'message' => 'Please send me a quote.',
                ] : null,
                'assigned_to' => $this->pick([$this->staff['admin'], $this->staff['sales'], $this->staff['staff']])->id,
                'lost_reason' => $status === 'lost' ? $this->pick(['Chose a cheaper forwarder', 'Customer postponed purchase', 'Budget too low for target vehicle']) : null,
                'won_at' => $status === 'won' ? $created->copy()->addDays(mt_rand(3, 20)) : null,
                'lost_at' => $status === 'lost' ? $created->copy()->addDays(mt_rand(3, 20)) : null,
                'next_follow_up' => in_array($status, ['won', 'lost']) ? null : $this->today->copy()->addDays(mt_rand(-3, 10))->setTime(10, 0),
            ]);
            $this->backdate($lead, $created);
            $leads[] = $lead;
        }

        return $leads;
    }

    private function seedCommunications(array $clients, array $leads): void
    {
        $templates = [
            ['email', 'outbound', 'Quote for your vehicle shipment', 'Sent the itemised quote with shipping, clearing and delivery.'],
            ['call', 'outbound', 'Follow-up call', 'Discussed loading date and payment schedule. Customer will confirm by Friday.'],
            ['call', 'inbound', 'Tracking enquiry', 'Customer asked for vessel ETA; shared tracking ID and expected arrival.'],
            ['email', 'inbound', 'Documents received', 'Customer sent title and bill of sale scans.'],
            ['meeting', 'outbound', 'Office visit', 'Met at the Lagos office to review clearing costs.'],
            ['note', 'internal', 'Internal note', 'Customer prefers updates on WhatsApp in the evening.'],
            ['email', 'outbound', 'Payment reminder', 'Reminded customer of the outstanding balance on the invoice.'],
            ['call', 'inbound', 'New vehicle request', 'Wants us to source a 2019 Lexus RX from auction.'],
        ];
        $targets = array_merge($clients, $leads, $clients);
        foreach ($targets as $i => $target) {
            if ($i % 3 === 2) {
                continue;
            }
            foreach (range(1, mt_rand(1, 3)) as $_) {
                [$type, $dir, $subject, $message] = $this->pick($templates);
                $at = $this->today->copy()->subDays(mt_rand(0, 150))->setTime(mt_rand(8, 19), mt_rand(0, 59));
                $c = Communication::create([
                    'communicable_type' => get_class($target),
                    'communicable_id' => $target->id,
                    'type' => $type,
                    'direction' => $dir,
                    'subject' => $subject,
                    'message' => $message,
                    'contacted_by' => $this->anyStaff()->id,
                    'contacted_at' => $at,
                ]);
                $this->backdate($c, $at);
            }
        }
    }

    // ---------------------------------------------------------------- operations

    private function seedContainers(): array
    {
        // [owner prefix, status, days since loaded (null = not loaded yet), size]
        $plan = [
            ['MSCU', 'released', 150, '40HC'], ['MAEU', 'released', 125, '40HC'],
            ['CMAU', 'released', 100, '40GP'], ['HLXU', 'offloaded', 70, '40HC'],
            ['GRIU', 'arrived', 52, '45HC'], ['MSCU', 'sailed', 30, '40HC'],
            ['ONEU', 'sailed', 18, '40HC'], ['MAEU', 'loaded', 6, '40GP'],
            ['CMAU', 'planned', null, '40HC'], ['MSCU', 'planned', null, '20GP'],
        ];
        $vessels = ['MSC ROSA M', 'MAERSK KENSINGTON', 'CMA CGM TAGE', 'GRANDE NIGERIA', 'HAPAG BERLIN EXPRESS', 'ONE CONTINUITY', 'GRANDE TEMA'];
        $order = array_keys(Container::getStatuses());
        $containers = [];

        foreach ($plan as $i => [$owner, $status, $ago, $size]) {
            $loaded = $ago !== null ? $this->today->copy()->subDays($ago) : null;
            $sailed = $loaded?->copy()->addDays(4);
            $eta = $sailed ? $sailed->copy()->addDays(32) : $this->today->copy()->addDays(20 + $i * 3);
            $reached = fn (string $s) => array_search($status, $order) >= array_search($s, $order);

            $container = Container::create([
                'container_number' => $this->containerNumber($owner),
                'size_type' => $size,
                'seal_number' => $loaded ? strtoupper(Str::random(2)) . mt_rand(100000, 999999) : null,
                'booking_number' => 'BK' . mt_rand(10000000, 99999999),
                'bl_number' => $loaded ? $owner . 'TOR' . mt_rand(100000, 999999) : null,
                'vessel' => $this->pick($vessels),
                'voyage' => mt_rand(100, 999) . $this->pick(['W', 'E', 'N', 'S']),
                'port_of_loading' => $this->pick(['MONTREAL', 'HALIFAX', 'TORONTO']),
                'port_of_discharge' => $this->pick(['LAGOS (APAPA)', 'LAGOS (TIN CAN)', 'LAGOS (LEKKI)']),
                'status' => $status,
                'capacity' => $size === '20GP' ? 2 : 4,
                'loaded_on' => $reached('loaded') ? $loaded : null,
                'etd' => $sailed ?? $this->today->copy()->addDays(5 + $i),
                'sailed_on' => $reached('sailed') ? $sailed : null,
                'eta' => $eta,
                'arrived_on' => $reached('arrived') ? $eta : null,
                'offloaded_on' => $reached('offloaded') ? $eta->copy()->addDays(5) : null,
                'released_on' => $reached('released') ? $eta->copy()->addDays(12) : null,
                'freight_cost' => $this->money(3800, 6200, 50),
                'freight_currency' => 'USD',
                'notes' => $this->pick([null, 'Shared container — 4 units.', 'Customs exam requested at destination.']),
                'created_by' => $this->staff['ops']->id,
            ]);
            $this->backdate($container, ($loaded ?? $this->today)->copy()->subDays(10));

            foreach (Container::CASCADE as $s => [$field]) {
                if ($container->{$field}) {
                    ShipmentMilestone::create([
                        'container_id' => $container->id, 'status' => $s,
                        'occurred_on' => $container->{$field}, 'user_id' => $this->staff['ops']->id,
                    ]);
                }
            }
            $containers[] = $container;
        }

        return $containers;
    }

    private function seedShipments(array $clients, array $containers): array
    {
        $cars = [
            ['TOYOTA', 'CAMRY', 'SE'], ['TOYOTA', 'HIGHLANDER', 'XLE'], ['TOYOTA', 'COROLLA', 'LE'], ['TOYOTA', 'RAV4', 'LIMITED'],
            ['TOYOTA', 'SIENNA', 'XLE'], ['TOYOTA', 'TACOMA', 'TRD'], ['LEXUS', 'RX 350', 'F SPORT'], ['LEXUS', 'ES 350', null],
            ['LEXUS', 'GX 460', 'PREMIUM'], ['HONDA', 'ACCORD', 'EX-L'], ['HONDA', 'CR-V', 'TOURING'], ['HONDA', 'PILOT', 'ELITE'],
            ['MERCEDES-BENZ', 'GLE 350', '4MATIC'], ['MERCEDES-BENZ', 'C 300', null], ['FORD', 'F-150', 'XLT'], ['FORD', 'EXPLORER', 'LIMITED'],
            ['HYUNDAI', 'SANTA FE', 'SEL'], ['KIA', 'SORENTO', 'EX'], ['NISSAN', 'PATHFINDER', 'SV'], ['ACURA', 'MDX', 'TECH'],
            ['BMW', 'X5', 'XDRIVE40I'], ['LAND ROVER', 'RANGE ROVER SPORT', 'HSE'], ['JEEP', 'GRAND CHEROKEE', 'LIMITED'],
        ];
        $colors = ['Black', 'White', 'Silver', 'Grey', 'Blue', 'Red', 'Champagne', 'Dark Green'];
        $platforms = ['COPART', 'IAA', 'MANHEIM', 'ADESA', 'DEALER', 'PRIVATE SELLER', 'CUSTOMER SUPPLIED', 'KIJIJI'];
        $cityFor = ['LAGOS (APAPA)' => 'Lagos', 'LAGOS (TIN CAN)' => 'Lagos', 'LAGOS (LEKKI)' => 'Lagos'];
        $buyers = $this->buyers($clients);
        $shipments = [];

        // Vehicles packed into containers inherit the container's progress.
        $containerStatusMap = [
            'planned' => 'at_yard', 'loaded' => 'loaded', 'sailed' => 'sailed',
            'arrived' => 'arrived', 'offloaded' => 'offloaded', 'released' => 'cleared',
        ];
        $jobs = [];
        foreach ($containers as $container) {
            $units = $container->status === 'planned' ? mt_rand(1, $container->capacity - 1) : $container->capacity;
            for ($slot = 1; $slot <= $units; $slot++) {
                $status = $containerStatusMap[$container->status];
                if ($container->status === 'released' && mt_rand(0, 3) > 0) {
                    $status = 'delivered';
                }
                $jobs[] = [$status, $container, $slot];
            }
        }
        // Loose vehicles still on the Canadian side, plus a couple of exceptions.
        foreach (['added', 'added', 'added', 'purchased', 'purchased', 'purchased', 'purchased', 'picked_up', 'picked_up', 'picked_up', 'at_yard', 'at_yard', 'at_yard', 'at_yard', 'on_hold', 'issue'] as $status) {
            $jobs[] = [$status, null, null];
        }

        $pipeline = ['added', 'purchased', 'picked_up', 'at_yard', 'loaded', 'sailed', 'arrived', 'offloaded', 'cleared', 'delivered'];

        foreach ($jobs as $n => [$status, $container, $slot]) {
            [$make, $model, $trim] = $this->pick($cars);
            $client = $this->pick($buyers);

            // Work out a believable timeline that ends at the shipment's current step.
            $loaded = $container?->loaded_on;
            $added = $loaded ? $loaded->copy()->subDays(mt_rand(20, 40)) : $this->today->copy()->subDays(mt_rand(1, 45));
            $purchased = $added->copy()->addDays(mt_rand(0, 4));
            $pickedUp = $purchased->copy()->addDays(mt_rand(3, 8));
            $atYard = $pickedUp->copy()->addDays(mt_rand(1, 3));
            if ($atYard->gt($this->today)) {
                $atYard = $this->today->copy();
            }
            $dates = [
                'added' => $added, 'purchased' => $purchased, 'picked_up' => $pickedUp, 'at_yard' => $atYard,
                'loaded' => $container?->loaded_on, 'sailed' => $container?->sailed_on, 'arrived' => $container?->arrived_on,
                'offloaded' => $container?->offloaded_on, 'cleared' => $container?->released_on,
                'delivered' => $container?->released_on?->copy()->addDays(mt_rand(1, 6)),
            ];
            $reachedIdx = in_array($status, $pipeline) ? array_search($status, $pipeline) : array_search($n % 2 ? 'purchased' : 'at_yard', $pipeline);
            $destination = $container?->port_of_discharge ?? $this->pick(['LAGOS (APAPA)', 'LAGOS (TIN CAN)', 'COTONOU']);
            $price = $this->money(4500, 32000, 50);

            $shipment = Shipment::create([
                'date_added' => $added,
                'vin' => $this->vin(),
                'vehicle_year' => mt_rand(2014, 2023),
                'vehicle_make' => $make,
                'vehicle_model' => $model,
                'vehicle_trim' => $trim,
                'vehicle_color' => $this->pick($colors),
                'has_keys' => mt_rand(0, 6) > 0,
                'tow_required' => mt_rand(0, 4) === 0,
                'driver_name' => $reachedIdx >= 2 ? $this->pick(['Pierre Lavoie', 'Ahmed Khan', 'Tom Wilson', 'Luc Bouchard']) : null,
                'purchase_platform' => $this->pick($platforms),
                'lot_number' => (string) mt_rand(40000000, 79999999),
                'purchase_price' => $price,
                'client_id' => $client->id,
                'container_id' => $container?->id,
                'container_slot' => $slot,
                'service_type' => $this->pick(['vehicle_export', 'vehicle_export', 'vehicle_export', 'freight_forwarding']),
                'status' => $status,
                'origin' => $container?->port_of_loading ?? $this->pick(['TORONTO', 'MONTREAL', 'HALIFAX']),
                'destination' => $destination,
                'destination_city' => $client->city ?? ($cityFor[$destination] ?? 'Lagos'),
                'location_notes' => $reachedIdx <= 3 ? $this->pick(['Mississauga yard, row C', 'Montreal yard, bay 4', 'Awaiting tow from auction lot']) : null,
                'estimated_completion' => $container?->eta?->copy()->addDays(14) ?? $this->today->copy()->addDays(mt_rand(40, 70)),
                'actual_completion' => $status === 'delivered' ? $dates['delivered'] : null,
                'total_value' => $price + $this->money(2600, 4200, 50),
                'assigned_to' => $this->pick([$this->staff['ops'], $this->staff['ops2'], $this->staff['admin']])->id,
                'priority' => $this->pick(['low', 'medium', 'medium', 'high']),
                'ownership_documents' => mt_rand(0, 1) ? ['title' => true, 'bill_of_sale' => true] : ['title' => mt_rand(0, 1) === 1],
                'notes' => $status === 'issue' ? 'Title mismatch with VIN — waiting on auction to correct paperwork.'
                    : ($status === 'on_hold' ? 'Customer asked to hold until balance is paid.' : $this->pick([null, null, 'Minor front bumper damage noted at pickup.', 'Spare key in glovebox.'])),
                'purchase_date' => $reachedIdx >= 1 ? $purchased : null,
                'loaded_on' => $dates['loaded'],
                'offloaded_on' => $reachedIdx >= 7 ? $dates['offloaded'] : null,
                'delivered_on' => $status === 'delivered' ? $dates['delivered'] : null,
                'date_received' => $reachedIdx >= 3 ? $atYard : null,
            ]);
            $this->backdate($shipment, $added->copy()->setTime(mt_rand(8, 17), mt_rand(0, 59)));

            for ($i = 0; $i <= $reachedIdx; $i++) {
                if ($dates[$pipeline[$i]]) {
                    ShipmentMilestone::create([
                        'shipment_id' => $shipment->id, 'container_id' => $i >= 4 ? $container?->id : null,
                        'status' => $pipeline[$i], 'occurred_on' => $dates[$pipeline[$i]],
                        'note' => $i >= 4 ? "Container {$container?->container_number}" : null,
                        'user_id' => $this->staff['ops']->id,
                    ]);
                }
            }
            if (in_array($status, ['on_hold', 'issue'])) {
                ShipmentMilestone::create([
                    'shipment_id' => $shipment->id, 'status' => $status,
                    'occurred_on' => $this->today->copy()->subDays(mt_rand(1, 6)), 'note' => $shipment->notes,
                    'user_id' => $this->staff['ops']->id,
                ]);
            }
            $shipments[] = $shipment;
        }

        return $shipments;
    }

    // ---------------------------------------------------------------- finance

    private function seedFinance(array $shipments): void
    {
        $methods = ['bank_transfer', 'bank_transfer', 'bank_transfer', 'cash', 'check', 'other'];
        $finance = $this->staff['finance'];

        foreach ($shipments as $shipment) {
            $base = $shipment->date_added;
            $costs = [
                ['Auction fees', $this->money(350, 900)],
                ['Towing', $shipment->status === 'added' ? 0 : $this->money(150, 450)],
                ['Storage', $this->money(0, 240)],
                ['Loading', $shipment->container_id ? $this->money(250, 400) : 0],
                ['Ocean freight', $shipment->container_id ? round((float) $shipment->container->freight_cost / max(1, $shipment->container->capacity), 2) : 0],
                ['Clearing', in_array($shipment->status, ['offloaded', 'cleared', 'delivered']) ? $this->money(900, 1800) : 0],
                ['Port charges', in_array($shipment->status, ['arrived', 'offloaded', 'cleared', 'delivered']) ? $this->money(200, 600) : 0],
            ];
            foreach ($costs as $i => [$category, $amount]) {
                if ($amount > 0) {
                    ShipmentCharge::create([
                        'shipment_id' => $shipment->id, 'kind' => 'cost', 'category' => $category,
                        'description' => $category === 'Ocean freight' ? 'Share of container freight' : null,
                        'amount' => $amount, 'currency' => 'USD', 'incurred_on' => $base->copy()->addDays($i * 6),
                        'created_by' => $finance->id,
                    ]);
                }
            }

            $billable = [
                ['Vehicle price', (float) $shipment->purchase_price],
                ['Shipping', $this->money(1800, 2600, 50)],
                ['Clearing', $this->money(1500, 2400, 50)],
                ['Delivery', in_array($shipment->destination_city, ['Lagos', 'Lekki', 'Ikeja', 'Apapa']) ? $this->money(150, 300, 50) : $this->money(350, 700, 50)],
                ['Service fee', 350.0],
            ];
            $charges = [];
            foreach ($billable as [$category, $amount]) {
                $charges[] = ShipmentCharge::create([
                    'shipment_id' => $shipment->id, 'kind' => 'charge', 'category' => $category,
                    'amount' => $amount, 'currency' => 'USD', 'incurred_on' => $base,
                    'created_by' => $finance->id,
                ]);
            }

            // Brand-new jobs aren't invoiced yet; everything else is.
            if (in_array($shipment->status, ['added']) && mt_rand(0, 2) > 0) {
                continue;
            }

            $issue = $base->copy()->addDays(mt_rand(0, 3));
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . $issue->format('ym') . '-' . str_pad((string) $shipment->id, 4, '0', STR_PAD_LEFT),
                'client_id' => $shipment->client_id,
                'issue_date' => $issue,
                'due_date' => $issue->copy()->addDays(14),
                'status' => mt_rand(0, 8) === 0 ? 'draft' : 'sent',
                'currency' => 'USD',
                'notes' => "Vehicle: {$shipment->vehicle_label} — VIN {$shipment->vin}",
                'created_by' => $finance->id,
            ]);
            $this->backdate($invoice, $issue->copy()->setTime(11, 0));
            foreach ($charges as $charge) {
                $charge->update(['invoice_id' => $invoice->id]);
            }
            $total = array_sum(array_map(fn ($c) => (float) $c->amount, $charges));

            // Later-stage shipments are mostly paid; early ones carry deposits.
            $stage = array_search($shipment->status, ['added', 'purchased', 'picked_up', 'at_yard', 'loaded', 'sailed', 'arrived', 'offloaded', 'cleared', 'delivered']);
            $paidShare = match (true) {
                $invoice->status === 'draft' => 0,
                $shipment->status === 'delivered' => 1,
                $stage === false => 0.3,
                $stage >= 6 => $this->pick([1, 1, 0.8]),
                $stage >= 3 => $this->pick([1, 0.7, 0.5]),
                default => $this->pick([0, 0.5, 0.6]),
            };
            $remaining = round($total * $paidShare, 2);
            $splits = $remaining > 0 ? ($paidShare < 1 || mt_rand(0, 1) ? [1.0] : [0.6, 0.4]) : [];
            foreach ($splits as $k => $share) {
                $amount = $k === count($splits) - 1 ? $remaining : round($remaining * $share, 2);
                $remaining = round($remaining - $amount, 2);
                $date = $issue->copy()->addDays(2 + $k * 15);
                if ($date->gt($this->today)) {
                    $date = $this->today->copy();
                }
                $payment = Payment::create([
                    'invoice_id' => $invoice->id, 'amount' => $amount, 'payment_date' => $date,
                    'payment_method' => $this->pick($methods),
                    'transaction_reference' => 'TRX' . mt_rand(10000000, 99999999),
                    'notes' => $k === 0 && $paidShare < 1 ? 'Deposit' : null,
                ]);
                $this->backdate($payment, $date->copy()->setTime(14, 0));
            }
            $invoice->recalculate();
        }

        // One voided invoice so the void state is visible.
        $void = Invoice::where('status', 'sent')->where('amount_paid', 0)->first();
        $void?->update(['status' => 'void', 'notes' => trim($void->notes . "\nVoided: duplicate of a reissued invoice.")]);
    }

    // ---------------------------------------------------------------- quoting

    private function seedQuotes(array $clients, array $leads, array $shipments): void
    {
        $templates = [
            ['Standard vehicle export (Canada → Lagos)', 'vehicle_export', [
                ['Ocean freight (shared 40HC)', 1, 1950], ['Loading & documentation', 1, 450], ['Clearing at Apapa/Tin Can', 1, 1850], ['Delivery within Lagos', 1, 200],
            ]],
            ['Auction purchase + shipping', 'vehicle_export', [
                ['Auction bidding service', 1, 300], ['Towing to yard', 1, 280], ['Ocean freight (shared 40HC)', 1, 1950], ['Clearing', 1, 1850],
            ]],
            ['Equipment export (flat rack)', 'freight_forwarding', [
                ['Flat rack freight', 1, 6800], ['Lashing & securing', 1, 900], ['Export documentation', 1, 350],
            ]],
            ['Inbound clearing only', 'inbound_clearing', [
                ['Customs duty processing', 1, 650], ['Terminal charges', 1, 420], ['Agency fee', 1, 300],
            ]],
        ];
        foreach ($templates as [$name, $service, $items]) {
            QuoteTemplate::create([
                'name' => $name, 'service_type' => $service,
                'line_items' => array_map(fn ($i) => ['description' => $i[0], 'quantity' => $i[1], 'unit_price' => $i[2]], $items),
                'terms' => "Quote valid for 14 days. Duties are estimates and may change at assessment. 50% deposit before loading.",
            ]);
        }

        $statuses = ['draft', 'draft', 'sent', 'sent', 'sent', 'pending', 'approved', 'approved', 'approved', 'approved', 'rejected', 'expired'];
        $buyers = $this->buyers($clients);
        $count = 22;
        for ($i = 1; $i <= $count; $i++) {
            $lead = $i % 2 ? $leads[($i * 3) % count($leads)] : null;
            $shipment = $i % 3 === 0 ? $shipments[($i * 5) % count($shipments)] : null;
            $client = $shipment?->client ?? $lead?->client ?? $this->pick($buyers);
            $status = $statuses[$i % count($statuses)];
            $issue = $this->today->copy()->subDays(mt_rand(1, 160));
            [, , $items] = $this->pick($templates);

            $quote = Quote::create([
                'quote_number' => 'QT-' . $issue->format('Y') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'lead_id' => $lead?->id,
                'shipment_id' => $shipment?->id,
                'status' => $status,
                'issue_date' => $issue,
                'expiry_date' => $issue->copy()->addDays(14),
                'notes' => $shipment ? "For {$shipment->vehicle_label} ({$shipment->tracking_id})" : ($lead?->title ?? 'General shipping quote'),
                'terms_conditions' => 'Quote valid for 14 days. 50% deposit before loading, balance before release.',
                'created_by' => $this->pick([$this->staff['sales'], $this->staff['admin']])->id,
            ]);
            $subtotal = 0;
            foreach ($items as [$desc, $qty, $price]) {
                $qty = $desc === 'Ocean freight (shared 40HC)' ? mt_rand(1, 3) : $qty;
                $amount = $qty * $price;
                $subtotal += $amount;
                QuoteItem::create(['quote_id' => $quote->id, 'description' => $desc, 'quantity' => $qty, 'unit_price' => $price, 'amount' => $amount]);
            }
            $tax = round($subtotal * 0.075, 2);
            $quote->update(['subtotal' => $subtotal, 'tax_total' => $tax, 'total_amount' => $subtotal + $tax]);
            $this->backdate($quote, $issue->copy()->setTime(10, 30));

            if ($status === 'approved' && mt_rand(0, 1)) {
                $p = Payment::create([
                    'quote_id' => $quote->id, 'amount' => round(($subtotal + $tax) * 0.5, 2),
                    'payment_date' => $issue->copy()->addDays(3)->min($this->today), 'payment_method' => 'bank_transfer',
                    'transaction_reference' => 'TRX' . mt_rand(10000000, 99999999), 'notes' => 'Quote deposit',
                ]);
                $this->backdate($p, $issue->copy()->addDays(3)->min($this->today));
            }
        }
    }

    // ---------------------------------------------------------------- inventory

    private function seedInventory(array $clients): void
    {
        $stock = [
            ['TOYOTA', 'CAMRY', 2019, 'White'], ['TOYOTA', 'HIGHLANDER', 2018, 'Black'], ['LEXUS', 'RX 350', 2017, 'Silver'],
            ['HONDA', 'ACCORD', 2020, 'Grey'], ['LEXUS', 'ES 350', 2019, 'Champagne'], ['TOYOTA', 'SIENNA', 2018, 'Blue'],
            ['MERCEDES-BENZ', 'GLE 350', 2018, 'Black'], ['FORD', 'EXPLORER', 2019, 'Red'], ['HYUNDAI', 'TUCSON', 2021, 'White'],
            ['KIA', 'SPORTAGE', 2020, 'Grey'], ['TOYOTA', 'COROLLA', 2021, 'Silver'], ['HONDA', 'CR-V', 2019, 'Black'],
            ['ACURA', 'RDX', 2019, 'White'], ['TOYOTA', '4RUNNER', 2018, 'Grey'], ['NISSAN', 'ROGUE', 2020, 'Blue'],
            ['LEXUS', 'GX 460', 2016, 'Black'], ['BMW', 'X3', 2019, 'White'], ['TOYOTA', 'TACOMA', 2020, 'Silver'],
        ];
        $statuses = ['in_stock', 'in_stock', 'in_stock', 'in_stock', 'in_yard', 'reserved', 'sold', 'shipped'];
        $suppliers = array_values(array_filter($clients, fn ($c) => $c->type !== 'buyer'));
        $buyers = $this->buyers($clients);
        $locations = ['Mississauga yard', 'Montreal yard', 'Lagos showroom (Lekki)', 'Apapa bonded terminal'];

        foreach ($stock as $i => [$make, $model, $year, $color]) {
            $status = $statuses[$i % count($statuses)];
            $bought = $this->today->copy()->subDays(mt_rand(10, 220));
            $cost = $this->money(6000, 26000, 100);
            $vehicle = Vehicle::create([
                'make' => $make, 'model' => $model, 'year' => $year, 'vin' => $this->vin(), 'color' => $color,
                'condition' => $this->pick(['used', 'used', 'used', 'new', 'salvage']),
                'status' => $status,
                'location' => $this->pick($locations),
                'purchase_price' => $cost,
                'sale_price' => $cost + $this->money(2500, 7000, 100),
                'purchase_date' => $bought,
                'sale_date' => in_array($status, ['sold', 'shipped']) ? $bought->copy()->addDays(mt_rand(10, 60))->min($this->today) : null,
                'buyer_id' => in_array($status, ['sold', 'shipped', 'reserved']) ? $this->pick($buyers)->id : null,
                'supplier_id' => $this->pick($suppliers)->id,
                'notes' => $this->pick([null, 'Clean title, one owner.', 'Fresh oil change and new tyres.', 'Minor scratches on rear bumper.']),
            ]);
            $this->backdate($vehicle, $bought);
            $this->yardLogs($vehicle, $bought, $status);
        }

        $machines = [
            ['Excavator', 'CATERPILLAR', '320D', 2015], ['Forklift', 'TOYOTA', '8FGU25', 2018], ['Generator', 'CUMMINS', 'C100 D6', 2019],
            ['Skid steer', 'BOBCAT', 'S650', 2017], ['Backhoe loader', 'JCB', '3CX', 2016], ['Wheel loader', 'KOMATSU', 'WA320', 2014],
            ['Forklift', 'HYSTER', 'H50FT', 2019], ['Compressor', 'ATLAS COPCO', 'XAS 185', 2020],
        ];
        foreach ($machines as $i => [$type, $make, $model, $year]) {
            $status = ['in_stock', 'in_stock', 'reserved', 'sold', 'shipped'][$i % 5];
            $bought = $this->today->copy()->subDays(mt_rand(20, 300));
            $cost = $this->money(9000, 65000, 500);
            $equipment = Equipment::create([
                'type' => $type, 'make' => $make, 'model' => $model, 'year' => $year,
                'serial_number' => strtoupper(Str::random(3)) . mt_rand(100000, 999999),
                'condition' => $this->pick(['used', 'used', 'new']),
                'status' => $status,
                'location' => $this->pick($locations),
                'purchase_price' => $cost,
                'sale_price' => $cost * 1.25,
                'purchase_date' => $bought,
                'buyer_id' => in_array($status, ['sold', 'shipped', 'reserved']) ? $this->pick($buyers)->id : null,
                'supplier_id' => $this->pick($suppliers)->id,
                'description' => "{$year} {$make} {$model} {$type}, " . mt_rand(1200, 9800) . ' hours, inspected and ready to ship.',
            ]);
            $this->backdate($equipment, $bought);
            $this->yardLogs($equipment, $bought, $status);
        }
    }

    private function yardLogs(Model $item, Carbon $bought, string $status): void
    {
        $steps = [['received', 'Received at yard'], ['inspected', 'Inspection done, photos taken']];
        if (in_array($status, ['reserved', 'sold', 'shipped'])) {
            $steps[] = ['reserved', 'Reserved for customer'];
        }
        if ($status === 'shipped') {
            $steps[] = ['released', 'Released to export'];
        }
        foreach ($steps as $i => [$s, $note]) {
            $at = $bought->copy()->addDays(2 + $i * 5)->min($this->today)->setTime(9 + $i, 15);
            YardLog::create([
                'inventory_type' => get_class($item), 'inventory_id' => $item->id, 'status' => $s,
                'logged_by' => $this->pick([$this->staff['ops'], $this->staff['ops2']])->id,
                'location' => $item->location, 'notes' => $note, 'logged_at' => $at,
            ]);
        }
    }

    // ---------------------------------------------------------------- tasks

    private function seedTasks(array $clients, array $leads, array $shipments): void
    {
        $list = [
            ['Call customer about loading date', 'shipment'], ['Collect title copy from auction', 'shipment'],
            ['Book tow from Copart Toronto', 'shipment'], ['Upload bill of lading', 'shipment'],
            ['Confirm duty assessment at Tin Can', 'shipment'], ['Arrange delivery to Abuja', 'shipment'],
            ['Chase outstanding balance', 'client'], ['Send updated price list', 'client'],
            ['Verify KYC documents', 'client'], ['Send quote for enquiry', 'lead'],
            ['Follow up on proposal', 'lead'], ['Qualify budget and timeline', 'lead'],
            ['Reconcile October payments', null], ['Prepare weekly container report', null],
            ['Renew yard insurance', null], ['Update vessel schedule on website', null],
        ];
        $assignees = [$this->staff['admin'], $this->staff['admin'], $this->staff['staff'], $this->staff['ops'], $this->staff['sales'], $this->staff['finance']];
        $i = 0;
        foreach (range(1, 2) as $round) {
            foreach ($list as [$title, $kind]) {
                $i++;
                $related = match ($kind) {
                    'shipment' => $this->pick($shipments),
                    'client' => $this->pick($clients),
                    'lead' => $this->pick($leads),
                    default => null,
                };
                $status = ['new', 'new', 'in_progress', 'completed', 'completed', 'cancelled', 'in_progress'][$i % 7];
                $created = $this->today->copy()->subDays(mt_rand(1, 40))->setTime(mt_rand(8, 17), 0);
                $assignee = $assignees[$i % count($assignees)];
                $due = $created->copy()->addDays(mt_rand(1, 14))->setTime(17, 0);
                if ($status !== 'completed' && $i % 4 === 0) {
                    $due = $this->today->copy()->subDays(mt_rand(1, 4))->setTime(17, 0); // overdue
                }
                $task = Task::create([
                    'title' => $related instanceof Shipment ? "{$title} — {$related->tracking_id}" : ($related ? "{$title} — " . ($related->name ?? $related->title) : $title),
                    'description' => $related instanceof Shipment ? "{$related->vehicle_label}, VIN {$related->vin}." : null,
                    'taskable_type' => $related ? get_class($related) : null,
                    'taskable_id' => $related?->id,
                    'assigned_to' => $assignee->id,
                    'assigned_by' => $this->staff['admin']->id,
                    'priority' => $this->pick(['low', 'medium', 'medium', 'high']),
                    'status' => $status,
                    'due_date' => $due,
                    'completed_by' => $status === 'completed' ? $assignee->id : null,
                    'completed_at' => $status === 'completed' ? $due->copy()->subHours(mt_rand(1, 30))->min(now()) : null,
                ]);
                $this->backdate($task, $created);
            }
        }
    }

    private function seedNotifications(): void
    {
        $admin = $this->staff['admin'];
        $rows = [];
        foreach (Lead::where('assigned_to', $admin->id)->latest()->take(4)->get() as $lead) {
            $rows[] = ['Modules\\Core\\Notifications\\LeadAssigned', ['type' => 'lead_assigned', 'title' => 'New Lead Assigned', 'body' => $lead->title, 'lead_id' => $lead->id]];
        }
        foreach (Task::where('assigned_to', $admin->id)->whereNotIn('status', ['completed', 'cancelled'])->where('due_date', '<', now())->take(3)->get() as $task) {
            $rows[] = ['Modules\\Core\\Notifications\\TaskOverdue', ['type' => 'task_overdue', 'title' => 'Task Overdue', 'body' => $task->title, 'task_id' => $task->id]];
        }
        foreach ($rows as $k => [$type, $data]) {
            $at = now()->subHours($k * 7 + 1);
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(), 'type' => $type, 'notifiable_type' => User::class, 'notifiable_id' => $admin->id,
                'data' => json_encode($data), 'read_at' => $k > 3 ? $at->copy()->addHour() : null,
                'created_at' => $at, 'updated_at' => $at,
            ]);
        }
    }
}
