<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\CRM\Entities\Client;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShippingLine;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function car(array $attributes = []): Shipment
    {
        return Shipment::create($attributes + [
            'vehicle_year' => 2015,
            'vehicle_make' => 'TOYOTA',
            'vehicle_model' => 'CAMRY',
            'status' => Shipment::STATUS_AT_YARD,
        ]);
    }

    public function test_new_car_gets_tracking_id_title_and_milestone(): void
    {
        $this->post(route('operations.shipments.store'), [
            'vin' => '1hgcm82633a004352',
            'vehicle_year' => 2003,
            'vehicle_make' => 'honda',
            'vehicle_model' => 'acord',
            'status' => 'added',
            'date_added' => '2026-10-20',
        ])->assertSessionHasNoErrors();

        $car = Shipment::firstOrFail();
        $this->assertSame('MAP26-10-001', $car->tracking_id);
        $this->assertSame('HONDA', $car->vehicle_make);
        $this->assertSame('ACCORD', $car->vehicle_model);
        $this->assertSame('1HGCM82633A004352', $car->vin);
        $this->assertSame('2003 HONDA ACCORD', $car->title);
        $this->assertSame(1, $car->milestones()->count());
    }

    public function test_unknown_make_is_rejected_with_suggestions(): void
    {
        $this->post(route('operations.shipments.store'), [
            'vehicle_make' => 'Qwxyz Motors',
            'status' => 'added',
        ])->assertSessionHasErrors('vehicle_make');

        $this->assertSame(0, Shipment::count());
    }

    public function test_duplicate_vin_is_rejected(): void
    {
        $this->car(['vin' => '1HGCM82633A004352']);

        $this->post(route('operations.shipments.store'), [
            'vin' => '1HGCM82633A004352',
            'status' => 'added',
        ])->assertSessionHasErrors('vin');
    }

    public function test_tracking_ids_increment_per_month(): void
    {
        $a = $this->car(['date_added' => '2026-10-01']);
        $b = $this->car(['date_added' => '2026-10-02']);
        $this->assertSame('MAP26-10-001', $a->tracking_id);
        $this->assertSame('MAP26-10-002', $b->tracking_id);
    }

    public function test_container_status_cascades_to_its_cars(): void
    {
        $this->post(route('operations.containers.store'), [
            'container_number' => 'hlcu 1234567',
            'shipping_line' => 'hapag lloyd',
            'shipment_ids' => [$this->car()->id, $this->car()->id],
        ])->assertSessionHasNoErrors();

        $container = Container::firstOrFail();
        $this->assertSame(ShippingLine::where('name', 'HAPAG-LLOYD')->value('id'), $container->shipping_line_id);
        $this->assertSame(2, $container->shipments()->count());

        $this->patch(route('operations.containers.update-status', $container), [
            'status' => 'sailed',
            'date' => '2026-10-21',
        ])->assertSessionHasNoErrors();

        foreach ($container->shipments as $car) {
            $this->assertSame(Shipment::STATUS_SAILED, $car->status);
            $this->assertTrue($car->milestones()->where('status', 'sailed')->exists());
        }
        $this->assertSame('2026-10-21', $container->fresh()->sailed_on->toDateString());
    }

    public function test_container_cannot_be_overfilled(): void
    {
        $container = Container::create(['container_number' => 'MSCU1234565', 'capacity' => 4]);
        $ids = collect(range(1, 5))->map(fn () => $this->car()->id)->all();

        $this->post(route('operations.containers.add-vehicles', $container), ['shipment_ids' => $ids])
            ->assertSessionHasErrors('shipment_ids');
        $this->assertSame(0, $container->shipments()->count());
    }

    public function test_car_added_to_moving_container_catches_up(): void
    {
        $container = Container::create(['container_number' => 'MSCU1234565']);
        $container->transitionTo('sailed', '2026-10-10');
        $car = $this->car();

        $this->post(route('operations.containers.add-vehicles', $container), ['shipment_ids' => [$car->id]])
            ->assertSessionHasNoErrors();

        $this->assertSame(Shipment::STATUS_SAILED, $car->fresh()->status);
        $this->assertSame(1, $car->fresh()->container_slot);
    }

    public function test_duplicate_open_container_is_blocked(): void
    {
        Container::create(['container_number' => 'MSCU1234565']);
        $this->post(route('operations.containers.store'), ['container_number' => 'MSCU1234565'])
            ->assertSessionHasErrors('container_number');
    }

    public function test_lookup_endpoint_resolves_typos(): void
    {
        $this->getJson(route('operations.lookups.suggest', ['type' => 'makes', 'q' => 'hyudai']))
            ->assertOk()
            ->assertJsonPath('resolved.value', 'HYUNDAI');

        $this->getJson(route('operations.lookups.suggest', ['type' => 'bogus', 'q' => 'x']))->assertNotFound();
    }

    public function test_catalog_blocks_near_duplicates_unless_forced(): void
    {
        $this->postJson(route('operations.lookups.store', 'makes'), ['name' => 'Toyotta'])->assertStatus(422);
        $this->postJson(route('operations.lookups.store', 'makes'), ['name' => 'Rivian'])->assertCreated();
    }

    public function test_vin_check_flags_existing_car(): void
    {
        $car = $this->car(['vin' => '1HGCM82633A004352']);
        $this->getJson(route('operations.vin.check', '1HGCM82633A004352'))
            ->assertOk()
            ->assertJsonPath('existing.tracking_id', $car->tracking_id);
    }

    public function test_bulk_status_update(): void
    {
        $cars = [$this->car(), $this->car()];
        $this->post(route('operations.shipments.bulk'), [
            'ids' => collect($cars)->pluck('id')->all(),
            'action' => 'status',
            'status' => 'delivered',
            'date' => '2026-10-25',
        ])->assertSessionHasNoErrors();

        foreach ($cars as $car) {
            $this->assertSame('delivered', $car->fresh()->status);
            $this->assertSame('2026-10-25', $car->fresh()->delivered_on->toDateString());
        }
    }

    public function test_pages_render(): void
    {
        $client = Client::create(['contact_person' => 'Ada Obi']);
        $car = $this->car(['client_id' => $client->id]);
        $container = Container::create(['container_number' => 'MSCU1234565']);

        $this->get(route('operations.shipments.index'))->assertOk()->assertInertia(fn (Assert $p) => $p->component('Operations/Shipments/Index'));
        $this->get(route('operations.shipments.index', ['view' => 'no_vin']))->assertOk();
        $this->get(route('operations.shipments.show', $car))->assertOk()->assertInertia(fn (Assert $p) => $p->component('Operations/Shipments/Show'));
        $this->get(route('operations.containers.index'))->assertOk()->assertInertia(fn (Assert $p) => $p->component('Operations/Containers/Index'));
        $this->get(route('operations.containers.show', $container))->assertOk();
        $this->get(route('operations.catalog.index'))->assertOk()->assertInertia(fn (Assert $p) => $p->component('Operations/Catalog/Index'));
        $this->get(route('finance.invoices.index'))->assertOk();
        $this->get(route('finance.payments.index'))->assertOk();
        $this->get(route('crm.clients.show', $client))->assertOk();
        $this->get(route('dashboard'))->assertOk();
    }
}
