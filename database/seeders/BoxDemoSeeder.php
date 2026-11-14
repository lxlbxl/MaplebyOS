<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Http\Controllers\ContainerController;

/**
 * Sample small cargo (boxes riding in car containers) for testing.
 * Run after DemoDataSeeder: php artisan db:seed --class=BoxDemoSeeder
 * Refuses to run if any boxes already exist.
 */
class BoxDemoSeeder extends Seeder
{
    private const ITEMS = [
        ['Clothes and shoes', 'Cartons', 3, 45, 0.30, 650_000],
        ['Auto parts (brake pads, filters, headlamps)', 'Box', 2, 60, 0.20, 1_800_000],
        ['Foodstuff and toiletries', 'Barrel', 2, 140, 0.44, 900_000],
        ['Baby items and stroller', 'Cartons', 4, 38, 0.50, 750_000],
        ['Laptops and phone accessories', 'Box', 1, 18, 0.06, 3_200_000],
        ['Kitchen appliances (blender, microwave)', 'Cartons', 3, 42, 0.36, 1_100_000],
        ['Personal effects', 'Suitcase', 3, 70, 0.45, 500_000],
        ['Tyres (set of 4)', 'Pieces', 4, 44, 0.48, 1_400_000],
        ['Hair products and cosmetics', 'Cartons', 5, 55, 0.25, 1_250_000],
        ['Gym equipment (dumbbells, bench)', 'Pallet', 1, 160, 0.90, 1_600_000],
        ['Books and school supplies', 'Box', 6, 90, 0.35, 400_000],
        ['TV (65 inch) and sound bar', 'Box', 2, 35, 0.32, 2_100_000],
        ['Used household goods', 'Bags', 6, 85, 0.70, 450_000],
        ['Engine and gearbox parts', 'Pallet', 1, 210, 0.80, 2_600_000],
        ['Wedding gifts and fabrics', 'Cartons', 3, 40, 0.28, 980_000],
        ['Generator (petrol, 5kVA)', 'Pieces', 1, 75, 0.40, 1_350_000],
    ];

    private const CITIES = ['Lagos', 'Lagos', 'Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Enugu', 'Benin City'];

    public function run(): void
    {
        if (Shipment::boxes()->exists()) {
            $this->command?->warn('Boxes already exist; skipping BoxDemoSeeder.');

            return;
        }

        mt_srand(4242);
        $clients = Client::inRandomOrder()->limit(20)->get();
        $staff = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Staff', 'Logistics Officer', 'Puncher']))->get();
        $by = $staff->first() ?? User::first();
        $containers = Container::forVehicles()->orderBy('id')->get();

        // How many boxes ride in each container (older containers carried more).
        $plan = [];
        foreach ($containers as $i => $container) {
            $plan[] = [$container, $i < 8 ? 2 : 1];
        }
        $plan[] = [null, 4]; // not in a container yet

        $made = 0;
        foreach ($plan as [$container, $count]) {
            for ($n = 0; $n < $count; $n++) {
                [$what, $type, $pkgs, $kg, $cbm, $value] = self::ITEMS[$made % count(self::ITEMS)];
                $client = $clients[$made % max(1, $clients->count())] ?? null;
                $city = self::CITIES[mt_rand(0, count(self::CITIES) - 1)];

                $added = $container?->loaded_on
                    ? Carbon::parse($container->loaded_on)->subDays(mt_rand(6, 25))
                    : Carbon::today()->subDays(mt_rand(1, 12));

                $box = Shipment::create([
                    'cargo_type' => Shipment::CARGO_BOX,
                    'service_type' => 'Box in car container',
                    'client_id' => $client?->id,
                    'consignee' => $client?->contact_person ?: $client?->company_name,
                    'cargo_description' => $what,
                    'package_count' => $pkgs,
                    'package_type' => $type,
                    'gross_weight_kg' => round($kg * (0.8 + mt_rand(0, 40) / 100), 1),
                    'volume_cbm' => round($cbm * (0.8 + mt_rand(0, 40) / 100), 3),
                    'declared_value' => $value,
                    'declared_currency' => 'NGN',
                    'destination_city' => $city,
                    'destination' => $city,
                    'date_added' => $added->toDateString(),
                    'status' => Shipment::STATUS_ADDED,
                    'assigned_to' => $staff->isNotEmpty() ? $staff[mt_rand(0, $staff->count() - 1)]->id : null,
                    'created_by' => $by?->id,
                ]);
                $box->milestones()->create([
                    'status' => Shipment::STATUS_ADDED, 'occurred_on' => $added, 'note' => 'Box booked', 'user_id' => $by?->id,
                ]);

                // Everything that will sail was received at the yard first.
                if ($container || $n % 2 === 0) {
                    $box->transitionTo(Shipment::STATUS_AT_YARD, $added->copy()->addDays(mt_rand(1, 4)), 'Received at Toronto yard');
                }

                if ($container) {
                    app(ContainerController::class)->assign($container, collect([$box->fresh()]));
                    $box->refresh();
                    // Released containers: most boxes have since reached the customer.
                    if ($box->status === Shipment::STATUS_CLEARED && $n === 0) {
                        $box->transitionTo(Shipment::STATUS_DELIVERED, Carbon::parse($container->released_on)->addDays(mt_rand(1, 5)), "Delivered to consignee in {$city}");
                    }
                }

                $this->charges($box->fresh(), $added, $kg, $city, $by?->id);
                $made++;
            }
        }

        // One box with a problem, for testing the issue flow.
        $odd = Shipment::boxes()->where('status', Shipment::STATUS_AT_YARD)->first();
        $odd?->transitionTo(Shipment::STATUS_ON_HOLD, Carbon::today(), 'Waiting for customer to confirm contents list');

        $this->command?->info("Created {$made} sample boxes.");
    }

    private function charges(Shipment $box, Carbon $on, int $kg, string $city, ?int $by): void
    {
        $shipping = max(150_000, (int) round($kg * 4_500 / 5_000) * 5_000);
        $lines = [
            ['charge', 'Shipping', $shipping],
            ['charge', 'Handling', 25_000],
            ['cost', 'Handling', 10_000],
            ['cost', 'Packing', mt_rand(1, 3) * 5_000],
        ];
        if (in_array($box->status, [Shipment::STATUS_CLEARED, Shipment::STATUS_DELIVERED, Shipment::STATUS_OFFLOADED], true)) {
            $lines[] = ['cost', 'Clearing', 35_000];
            $lines[] = ['charge', 'Clearing', 60_000];
        }
        if ($city !== 'Lagos') {
            $lines[] = ['cost', 'Delivery', 40_000];
            $lines[] = ['charge', 'Delivery', 65_000];
        }

        foreach ($lines as [$kind, $category, $amount]) {
            ShipmentCharge::create([
                'shipment_id' => $box->id, 'kind' => $kind, 'category' => $category,
                'description' => $category === 'Delivery' ? "Lagos to {$city}" : null,
                'amount' => $amount, 'currency' => 'NGN', 'incurred_on' => $on->toDateString(), 'created_by' => $by,
            ]);
        }
    }
}
