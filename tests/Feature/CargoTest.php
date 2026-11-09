<?php

namespace Tests\Feature;

use App\Support\Access;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\Invoice;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Tests\TestCase;

/**
 * General cargo clearing jobs and boxes riding in car containers.
 */
class CargoTest extends TestCase
{
    use RefreshDatabase;

    private function carContainer(string $number = 'MSCU1234565'): Container
    {
        return Container::create(['container_number' => $number, 'status' => Container::STATUS_PLANNED, 'capacity' => 4]);
    }

    private function car(array $attrs = []): Shipment
    {
        return Shipment::create($attrs + ['vehicle_make' => 'TOYOTA', 'vehicle_model' => 'CAMRY', 'vehicle_year' => 2018]);
    }

    public function test_existing_rows_default_to_vehicles_with_map_ids(): void
    {
        $car = $this->car();

        $this->assertSame(Shipment::CARGO_VEHICLE, $car->fresh()->cargo_type);
        $this->assertMatchesRegularExpression('/^MAP\d{2}-\d{2}-001$/', $car->tracking_id);
        $this->assertSame(Shipment::STATUS_ADDED, $car->status);
    }

    public function test_clearing_job_is_opened_with_containers_and_its_own_ids(): void
    {
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $client = Client::create(['company_name' => 'Okafor Imports', 'contact_person' => 'Emeka Okafor']);

        $this->post(route('operations.clearing.store'), [
            'client_id' => $client->id,
            'cargo_description' => 'Ceramic tiles',
            'hs_code' => '6907.21.00',
            'package_count' => 1200,
            'package_type' => 'Cartons',
            'bl_number' => 'medu 1234567',
            'form_m_number' => 'MF20260012345',
            'terminal' => 'Tin Can Island (TICT)',
            'eta' => '2026-11-20',
            'containers' => "MSCU1234565 40HC\nTGHU 123456 7, 20",
        ])->assertSessionHasNoErrors()->assertRedirect();

        $job = Shipment::generalCargo()->sole();
        $this->assertMatchesRegularExpression('/^CLR\d{2}-\d{2}-001$/', $job->tracking_id);
        $this->assertSame(Shipment::STATUS_AWAITING_DOCS, $job->status);
        $this->assertSame('MEDU1234567', $job->bl_number);
        $this->assertSame('Ceramic tiles', $job->title);
        $this->assertSame('Lagos', $job->destination_city);

        $containers = $job->cargoContainers;
        $this->assertCount(2, $containers);
        $this->assertSame(['MSCU1234565', 'TGHU1234567'], $containers->pluck('container_number')->all());
        $this->assertSame(['40HC', '20GP'], $containers->pluck('size_type')->all());
        $this->assertSame('MEDU1234567', $containers->first()->bl_number);

        // A clearing job never shows up among the cars, and its containers stay off the car board.
        $this->get(route('operations.shipments.index'))->assertInertia(fn ($page) => $page->where('shipments.total', 0));
        $this->get(route('operations.containers.index'))->assertInertia(fn ($page) => $page->has('containers', 0));
        $this->get(route('operations.clearing.index'))->assertInertia(fn ($page) => $page->where('jobs.total', 1));
        $this->get(route('operations.clearing.show', $job))->assertOk();
        $this->get(route('operations.shipments.show', $job))->assertRedirect(route('operations.clearing.show', $job));
    }

    public function test_clearing_stages_stamp_dates_and_move_containers(): void
    {
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $this->post(route('operations.clearing.store'), [
            'cargo_description' => 'Spare parts', 'containers' => 'MSCU1234565',
        ])->assertSessionHasNoErrors();
        $job = Shipment::generalCargo()->sole();

        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'arrived', 'date' => '2026-11-21'])->assertSessionHasNoErrors();
        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'duty_paid', 'date' => '2026-11-24'])->assertSessionHasNoErrors();
        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'released', 'date' => '2026-11-26'])->assertSessionHasNoErrors();
        // Car-only statuses are refused.
        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'loaded'])->assertSessionHasErrors('status');

        $job->refresh();
        $this->assertSame('released', $job->status);
        $this->assertSame('Customs released', $job->status_label);
        $this->assertSame('2026-11-21', $job->arrived_on->toDateString());
        $this->assertSame('2026-11-24', $job->duty_paid_on->toDateString());
        $this->assertSame('2026-11-26', $job->released_on->toDateString());
        $this->assertSame(['awaiting_docs', 'arrived', 'duty_paid', 'released'], $job->milestones->pluck('status')->reverse()->values()->all());

        $container = $job->cargoContainers->first();
        $this->assertSame(Container::STATUS_RELEASED, $container->status);
        $this->assertSame('2026-11-21', $container->arrived_on->toDateString());
        $this->assertSame('2026-11-26', $container->released_on->toDateString());

        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'empty_returned', 'date' => '2026-12-01']);
        $this->assertSame('2026-12-01', $container->fresh()->empty_returned_on->toDateString());
    }

    public function test_container_cannot_sit_on_two_jobs_or_be_a_car_container(): void
    {
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $this->carContainer('MSCU1234565');

        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Tiles', 'containers' => 'MSCU1234565'])
            ->assertSessionHasErrors('containers');
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Tiles', 'containers' => 'NOT-A-BOX'])
            ->assertSessionHasErrors('containers');
        $this->assertSame(0, Shipment::generalCargo()->count());

        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Tiles', 'bl_number' => 'BL1', 'containers' => 'TGHU1234567'])->assertSessionHasNoErrors();
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Rice', 'bl_number' => 'BL2', 'containers' => 'TGHU1234567'])
            ->assertSessionHasErrors('containers');
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Rice', 'bl_number' => 'bl1'])
            ->assertSessionHasErrors('bl_number');

        // Cars cannot be put into a clearing job's container.
        $cargoBox = Container::where('container_number', 'TGHU1234567')->sole();
        $car = $this->car();
        $this->post(route('operations.containers.add-vehicles', $cargoBox), ['shipment_ids' => [$car->id]])->assertSessionHasErrors('shipment_ids');
    }

    public function test_boxes_ride_in_car_containers_without_taking_slots_and_follow_the_container(): void
    {
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $owner = Client::create(['contact_person' => 'Chika Eze']);
        $container = $this->carContainer();
        $cars = collect(range(1, 4))->map(fn () => $this->car());
        $this->post(route('operations.containers.add-vehicles', $container), ['shipment_ids' => $cars->pluck('id')->all()])->assertSessionHasNoErrors();
        $this->assertSame(0, $container->freeSlots());

        // A full container still takes a box.
        $this->post(route('operations.boxes.store'), [
            'client_id' => $owner->id,
            'cargo_description' => 'Clothes and shoes',
            'package_count' => 3,
            'package_type' => 'Box',
            'volume_cbm' => 0.8,
            'container_id' => $container->id,
        ])->assertSessionHasNoErrors();

        $box = Shipment::boxes()->sole();
        $this->assertMatchesRegularExpression('/^BOX\d{2}-\d{2}-001$/', $box->tracking_id);
        $this->assertSame($container->id, $box->container_id);
        $this->assertNull($box->container_slot);
        $this->assertSame(4, $container->vehicles()->count());
        $this->assertSame(1, $container->boxes()->count());

        // Loading, sailing and customs release of the container move the box too.
        $container->transitionTo(Container::STATUS_LOADED, '2026-11-10');
        $container->transitionTo(Container::STATUS_SAILED, '2026-11-12');
        $container->fresh()->transitionTo(Container::STATUS_RELEASED, '2026-12-28');
        $box->refresh();
        $this->assertSame(Shipment::STATUS_CLEARED, $box->status);
        $this->assertSame('Cleared', $box->status_label);
        $this->assertSame('2026-11-10', $box->loaded_on->toDateString());
        $this->assertEqualsCanonicalizing(['added', 'loaded', 'sailed', 'cleared'], $box->milestones->pluck('status')->all());

        // A box joining a container that already sailed catches up.
        $this->post(route('operations.boxes.store'), ['cargo_description' => 'Auto parts', 'container_id' => $container->id])->assertSessionHasNoErrors();
        $this->assertSame(Shipment::STATUS_CLEARED, Shipment::boxes()->latest('id')->first()->status);

        // Boxes are not counted in the cars' freight split or the car list.
        $container->update(['freight_cost' => 4000]);
        $this->assertEquals(1000, $container->freightSharePerVehicle());
        $this->get(route('operations.shipments.index'))->assertInertia(fn ($page) => $page->where('shipments.total', 4));
        $this->get(route('operations.boxes.index', ['view' => 'all']))->assertInertia(fn ($page) => $page->where('boxes.total', 2));
        $this->get(route('operations.boxes.show', $box))->assertOk();
    }

    public function test_box_cannot_go_into_a_clearing_container(): void
    {
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Tiles', 'containers' => 'TGHU1234567']);
        $cargoContainer = Container::sole();

        $this->post(route('operations.boxes.store'), ['cargo_description' => 'Clothes', 'container_id' => $cargoContainer->id])
            ->assertSessionHasErrors('container_id');
    }

    public function test_clearing_money_uses_clearing_categories_naira_and_separate_invoices(): void
    {
        $this->actingAs($this->staff(Access::ROLE_CEO));
        $client = Client::create(['company_name' => 'Okafor Imports', 'contact_person' => 'Emeka Okafor']);
        $this->post(route('operations.clearing.store'), ['client_id' => $client->id, 'cargo_description' => 'Tiles', 'duty_assessed' => 2500000]);
        $job = Shipment::generalCargo()->sole();
        $this->assertEquals(2500000, $job->duty_assessed);

        $this->post(route('finance.charges.store', $job), ['kind' => 'cost', 'category' => 'Customs duty', 'amount' => 2500000])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $job), ['kind' => 'charge', 'category' => 'Agency fee', 'amount' => 350000])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $job), ['kind' => 'charge', 'category' => 'Vehicle price', 'amount' => 1])->assertSessionHasErrors('category');
        $this->assertSame(['NGN'], ShipmentCharge::pluck('currency')->unique()->values()->all());

        // A car charge in USD for the same client can't share an invoice with naira lines.
        $car = $this->car(['client_id' => $client->id]);
        $this->post(route('finance.charges.store', $car), ['kind' => 'charge', 'category' => 'Shipping', 'amount' => 1800])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $car), ['kind' => 'charge', 'category' => 'Agency fee', 'amount' => 1])->assertSessionHasErrors('category');

        $charges = ShipmentCharge::where('kind', 'charge')->pluck('id')->all();
        $this->post(route('finance.invoices.store'), ['client_id' => $client->id, 'charge_ids' => $charges])->assertSessionHasErrors('charge_ids');

        $naira = ShipmentCharge::where('kind', 'charge')->where('currency', 'NGN')->pluck('id')->all();
        $this->post(route('finance.invoices.store'), ['client_id' => $client->id, 'charge_ids' => $naira])->assertSessionHasNoErrors();
        $invoice = Invoice::sole();
        $this->assertSame('NGN', $invoice->currency);
        $this->assertEquals(350000, $invoice->total);

        $this->get(route('operations.clearing.show', $job))->assertInertia(fn ($page) => $page
            ->where('money.NGN.cost', 2500000)->where('money.NGN.charge', 350000)->where('money.NGN.profit', -2150000));
    }

    public function test_roles_and_money_hiding_on_cargo(): void
    {
        $this->actingAs($this->staff(Access::ROLE_CEO));
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Tiles', 'duty_assessed' => 900000, 'declared_value' => 40000]);
        $job = Shipment::generalCargo()->sole();

        $this->actingAs($this->staff(Access::ROLE_PUNCHER));
        $this->get(route('operations.clearing.show', $job))->assertOk()->assertInertia(fn ($page) => $page
            ->missing('job.duty_assessed')->missing('job.declared_value')->where('money', null));
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Rice'])->assertForbidden();
        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'arrived'])->assertForbidden();
    }

    public function test_public_tracking_finds_boxes_and_clearing_jobs(): void
    {
        $job = Shipment::create(['cargo_type' => Shipment::CARGO_GENERAL, 'cargo_description' => 'Tiles', 'bl_number' => 'MEDU1234567']);
        $job->transitionTo(Shipment::STATUS_ARRIVED, '2026-11-21');
        $box = Shipment::create(['cargo_type' => Shipment::CARGO_BOX, 'cargo_description' => 'Clothes']);

        $this->getJson('/tracking/lookup?q=MEDU1234567')->assertOk()
            ->assertJson(['found' => true, 'tracking_id' => $job->tracking_id, 'status_label' => 'Arrived port', 'cargo_type' => 'general_cargo'])
            ->assertJsonCount(9, 'steps');
        $this->getJson('/tracking/lookup?q=' . $box->tracking_id)->assertOk()
            ->assertJson(['found' => true, 'vehicle' => 'Clothes', 'status_label' => 'Booked'])
            ->assertJsonCount(8, 'steps');
        $this->assertStringStartsWith('BOX', $box->tracking_id);
    }

    public function test_dashboard_counts_cars_and_dollars_only(): void
    {
        $client = Client::create(['company_name' => 'Okafor Imports', 'contact_person' => 'Emeka Okafor']);
        // No VIN and not in a container, but neither is a car.
        Shipment::create(['cargo_type' => Shipment::CARGO_BOX, 'cargo_description' => 'Clothes', 'date_added' => now()->subDays(60)]);
        $job = Shipment::create(['cargo_type' => Shipment::CARGO_GENERAL, 'cargo_description' => 'Tiles', 'client_id' => $client->id]);
        $job->charges()->create(['kind' => 'charge', 'category' => 'Agency fee', 'amount' => 350000, 'currency' => 'NGN']);
        $this->actingAs($this->staff(Access::ROLE_CEO));
        $this->post(route('finance.invoices.store'), ['client_id' => $client->id, 'charge_ids' => $job->charges()->pluck('id')->all()])->assertSessionHasNoErrors();
        Invoice::sole()->update(['status' => 'sent']);

        $this->get(route('dashboard'))->assertOk()->assertInertia(fn ($page) => $page
            ->where('data.money.outstanding', 0)
            ->where('data.alerts', fn ($alerts) => collect($alerts)->every(fn ($a) => !str_contains($a['text'], 'car(s)'))));
    }
}
