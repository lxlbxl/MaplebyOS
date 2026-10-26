<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\Invoice;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Tests\TestCase;

class FinanceAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function carFor(Client $client): Shipment
    {
        return Shipment::create([
            'client_id' => $client->id,
            'vin' => '1HGCM82633A004352',
            'vehicle_year' => 2003,
            'vehicle_make' => 'HONDA',
            'vehicle_model' => 'ACCORD',
            'status' => Shipment::STATUS_AT_YARD,
        ]);
    }

    public function test_charges_invoice_and_payments_flow(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['contact_person' => 'Ada Obi']);
        $car = $this->carFor($client);

        $chargeCategory = ShipmentCharge::CATEGORIES['charge'][0];
        $costCategory = ShipmentCharge::CATEGORIES['cost'][0];

        $this->post(route('finance.charges.store', $car), ['kind' => 'charge', 'category' => $chargeCategory, 'amount' => 1500])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $car), ['kind' => 'charge', 'category' => $chargeCategory, 'amount' => 500])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $car), ['kind' => 'cost', 'category' => $costCategory, 'amount' => 900])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $car), ['kind' => 'cost', 'category' => $chargeCategory === $costCategory ? 'nope' : $chargeCategory, 'amount' => 1])
            ->assertSessionHasErrors('category');

        $charges = ShipmentCharge::where('kind', 'charge')->pluck('id')->all();
        $cost = ShipmentCharge::where('kind', 'cost')->value('id');

        // A cost line cannot be billed to the customer.
        $this->post(route('finance.invoices.store'), ['client_id' => $client->id, 'charge_ids' => [$cost]])->assertSessionHasErrors();

        $this->post(route('finance.invoices.store'), ['client_id' => $client->id, 'charge_ids' => $charges])->assertSessionHasNoErrors();
        $invoice = Invoice::sole();
        $this->assertEquals(2000, $invoice->total);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{4}$/', $invoice->invoice_number);

        $pay = fn ($amount) => $this->post(route('finance.payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => $amount, 'payment_date' => '2026-10-25', 'payment_method' => 'bank_transfer',
        ])->assertSessionHasNoErrors();

        $pay(500);
        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertEquals(1500, $invoice->fresh()->balance);

        $pay(1500);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('paid', $car->fresh()->payment_status);

        $this->get(route('finance.invoices.show', $invoice))->assertOk();
    }

    public function test_voiding_an_invoice_frees_its_charges(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['contact_person' => 'Ada Obi']);
        $car = $this->carFor($client);
        $charge = $car->charges()->create(['kind' => 'charge', 'category' => ShipmentCharge::CATEGORIES['charge'][0], 'amount' => 100]);

        $this->post(route('finance.invoices.store'), ['client_id' => $client->id, 'charge_ids' => [$charge->id]]);
        $invoice = Invoice::sole();

        $this->patch(route('finance.invoices.update-status', $invoice), ['status' => 'void'])->assertSessionHasNoErrors();
        $this->assertNull($charge->fresh()->invoice_id);
    }

    public function test_public_tracking_lookup_hides_private_data(): void
    {
        $client = Client::create(['contact_person' => 'Ada Obi', 'phone' => '0800']);
        $car = $this->carFor($client);
        $container = Container::create(['container_number' => 'MSCU1234565']);
        $car->update(['container_id' => $container->id]);
        $container->transitionTo('sailed', '2026-10-10');

        $response = $this->getJson(route('website.tracking.lookup', ['q' => strtolower($car->tracking_id)]))
            ->assertOk()
            ->assertJsonPath('status', 'sailed')
            ->assertJsonPath('vin_last6', '004352')
            ->assertJsonPath('container.number', 'MSCU1234565');

        $body = $response->getContent();
        $this->assertStringNotContainsString('Ada Obi', $body);
        $this->assertStringNotContainsString('1HGCM82633A004352', $body);

        $this->getJson(route('website.tracking.lookup', ['q' => '1HGCM82633A004352']))->assertOk();
        $this->getJson(route('website.tracking.lookup', ['q' => 'HONDA ACCORD']))->assertNotFound();
        $this->get(route('website.tracking'))->assertOk();
    }

    public function test_operations_screens_require_login(): void
    {
        $this->get(route('operations.shipments.index'))->assertRedirect();
        $this->getJson(route('operations.lookups.suggest', ['type' => 'customers', 'q' => 'ada']))->assertUnauthorized();
    }
}
