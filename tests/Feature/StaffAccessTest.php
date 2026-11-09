<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Access;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Document;
use Modules\Operations\Entities\Shipment;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attributes = []): Shipment
    {
        return Shipment::create($attributes + [
            'vin' => '1HGCM82633A004352',
            'vehicle_year' => 2003,
            'vehicle_make' => 'HONDA',
            'vehicle_model' => 'ACCORD',
            'status' => Shipment::STATUS_AT_YARD,
            'purchase_price' => 4200,
        ]);
    }

    public function test_every_role_gets_its_own_home(): void
    {
        $homes = [
            Access::ROLE_CEO => 'ceo',
            Access::ROLE_BUSINESS => 'business',
            Access::ROLE_LOGISTICS => 'logistics',
            Access::ROLE_PUNCHER => 'puncher',
        ];
        $this->car();
        Container::create(['container_number' => 'MSCU1234565', 'status' => Container::STATUS_SAILED, 'eta' => now()->addDays(3)]);

        foreach ($homes as $role => $home) {
            $this->actingAs($this->staff($role))
                ->get(route('dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $p) => $p->component('Home/Index')->where('home', $home)->has('data'));
        }
    }

    public function test_puncher_and_logistics_never_receive_money(): void
    {
        $client = Client::create(['contact_person' => 'Ada Obi', 'phone' => '+2348000000000', 'email' => 'ada@example.com']);
        $car = $this->car(['client_id' => $client->id]);
        $car->charges()->create(['kind' => 'cost', 'category' => ShipmentCharge::CATEGORIES['cost'][0], 'amount' => 900]);

        foreach ([Access::ROLE_PUNCHER, Access::ROLE_LOGISTICS] as $role) {
            $this->actingAs($this->staff($role))
                ->get(route('operations.shipments.show', $car))
                ->assertOk()
                ->assertInertia(fn (Assert $p) => $p
                    ->where('money', null)
                    ->where('totalPaid', null)
                    ->missing('shipment.purchase_price')
                    ->missing('shipment.charges')
                    ->missing('shipment.client.phone')
                    ->missing('shipment.client.email')
                    ->where('shipment.client.contact_person', 'Ada Obi'));

            $this->get(route('operations.shipments.index'))
                ->assertInertia(fn (Assert $p) => $p->missing('shipments.data.0.purchase_price'));
        }

        $this->actingAs($this->staff(Access::ROLE_BUSINESS))
            ->get(route('operations.shipments.show', $car))
            ->assertInertia(fn (Assert $p) => $p->where('money.total_cost', 5100)->where('shipment.purchase_price', '4200.00'));
    }

    public function test_restricted_screens_are_blocked_per_role(): void
    {
        $car = $this->car();
        $container = Container::create(['container_number' => 'MSCU1234565']);

        $this->actingAs($this->staff(Access::ROLE_PUNCHER));
        $this->get(route('finance.invoices.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('settings.index'))->assertForbidden();
        $this->get(route('crm.clients.index'))->assertForbidden();
        $this->post(route('operations.containers.store'), ['container_number' => 'MSCU7654321'])->assertForbidden();
        $this->patch(route('operations.shipments.update-status', $car), ['status' => 'loaded'])->assertForbidden();
        $this->post(route('operations.shipments.import'))->assertForbidden();
        $this->delete(route('operations.shipments.destroy', $car))->assertForbidden();

        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $this->patch(route('operations.containers.update-status', $container), ['status' => Container::STATUS_LOADED])->assertSessionHasNoErrors()->assertRedirect();
        $this->get(route('finance.payments.index'))->assertForbidden();
        $this->post(route('finance.charges.store', $car), ['kind' => 'charge', 'category' => ShipmentCharge::CATEGORIES['charge'][0], 'amount' => 10])->assertForbidden();
        $this->delete(route('operations.containers.destroy', $container))->assertForbidden();

        $this->actingAs($this->staff(Access::ROLE_BUSINESS));
        $this->get(route('finance.invoices.index'))->assertOk();
        $this->post(route('finance.charges.store', $car), ['kind' => 'charge', 'category' => ShipmentCharge::CATEGORIES['charge'][0], 'amount' => 10])->assertSessionHasNoErrors();
        $this->post(route('finance.charges.store', $car), ['kind' => 'cost', 'category' => ShipmentCharge::CATEGORIES['cost'][0], 'amount' => 10])->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->delete(route('operations.shipments.destroy', $car))->assertForbidden();

        $this->actingAs($this->staff(Access::ROLE_CEO));
        $this->delete(route('operations.shipments.destroy', $car))->assertRedirect();
    }

    public function test_puncher_edits_own_cars_only_until_loaded_and_cannot_set_money(): void
    {
        $puncher = $this->staff(Access::ROLE_PUNCHER);
        $this->actingAs($puncher)->post(route('operations.shipments.store'), [
            'vin' => '1HGCM82633A004352', 'vehicle_year' => 2003, 'vehicle_make' => 'HONDA', 'vehicle_model' => 'ACCORD',
            'purchase_price' => 99999, 'status' => Shipment::STATUS_DELIVERED,
        ])->assertSessionHasNoErrors();

        $mine = Shipment::sole();
        $this->assertSame($puncher->id, (int) $mine->created_by);
        $this->assertNull($mine->purchase_price, 'Puncher cannot set the purchase price');
        $this->assertSame(Shipment::STATUS_ADDED, $mine->status, 'Puncher cannot set the status');

        $this->put(route('operations.shipments.update', $mine), ['vehicle_color' => 'BLUE'])->assertSessionHasNoErrors();
        $this->assertSame('BLUE', $mine->fresh()->vehicle_color);

        $someoneElses = Shipment::create(['vin' => '5YJSA1E26HF000337', 'vehicle_make' => 'TESLA']);
        $this->put(route('operations.shipments.update', $someoneElses), ['vehicle_color' => 'RED'])->assertForbidden();

        $mine->update(['status' => Shipment::STATUS_LOADED]);
        $this->put(route('operations.shipments.update', $mine), ['vehicle_color' => 'GREEN'])->assertForbidden();
    }

    public function test_documents_by_role(): void
    {
        Storage::fake('local');
        $car = $this->car();
        $container = Container::create(['container_number' => 'MSCU1234565', 'status' => Container::STATUS_SAILED]);

        // Logistics scans the Bill of Lading against the container.
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS))
            ->post(route('documents.store'), [
                'documentable' => 'container', 'documentable_id' => $container->id, 'type' => 'bill_of_lading',
                'files' => [UploadedFile::fake()->image('bl-page1.jpg'), UploadedFile::fake()->image('bl-page2.jpg')],
            ])->assertSessionHasNoErrors();
        $this->assertSame(2, $container->documents()->count());
        Storage::disk('local')->assertExists($container->documents()->first()->path);

        // ...and it shows on each car in that container, and the container no longer lists it as missing.
        $car->update(['container_id' => $container->id]);
        $this->get(route('operations.shipments.show', $car))
            ->assertInertia(fn (Assert $p) => $p->has('documents.container_documents', 2));
        $this->get(route('operations.containers.show', $container))
            ->assertInertia(fn (Assert $p) => $p->where('documents.missing', fn ($missing) => !collect($missing)->contains('type', 'bill_of_lading')));

        // Price-bearing paper: logistics may not upload it...
        $this->post(route('documents.store'), [
            'documentable' => 'vehicle', 'documentable_id' => $car->id, 'type' => 'purchase_receipt',
            'files' => [UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf')],
        ])->assertForbidden();

        // ...the CEO uploads it, logistics can't open it, Head of Business can.
        $this->actingAs($this->staff(Access::ROLE_CEO))->post(route('documents.store'), [
            'documentable' => 'vehicle', 'documentable_id' => $car->id, 'type' => 'purchase_receipt',
            'files' => [UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf')],
        ])->assertSessionHasNoErrors();
        $receipt = Document::where('type', 'purchase_receipt')->sole();
        $this->assertTrue($receipt->sensitive);

        $this->actingAs($this->staff(Access::ROLE_LOGISTICS))->get(route('documents.show', $receipt))->assertForbidden();
        $this->get(route('operations.shipments.show', $car))
            ->assertInertia(fn (Assert $p) => $p->has('documents.documents', 0));
        $this->actingAs($this->staff(Access::ROLE_BUSINESS))->get(route('documents.show', $receipt))->assertOk();

        // Wrong file types are refused; only the CEO deletes.
        $this->actingAs($this->staff(Access::ROLE_PUNCHER))->post(route('documents.store'), [
            'documentable' => 'vehicle', 'documentable_id' => $car->id, 'type' => 'title',
            'files' => [UploadedFile::fake()->create('virus.exe', 10)],
        ])->assertSessionHasErrors('files.0');
        $this->delete(route('documents.destroy', $receipt))->assertForbidden();
        $this->actingAs($this->staff(Access::ROLE_CEO))->delete(route('documents.destroy', $receipt))->assertRedirect();
        $this->assertSoftDeleted($receipt);
    }

    public function test_deactivated_staff_cannot_sign_in_or_stay_signed_in(): void
    {
        $user = $this->staff(Access::ROLE_PUNCHER);
        $user->is_active = false;
        $user->save();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_management_assigns_staff_roles_and_keeps_a_ceo(): void
    {
        $ceo = $this->staff(Access::ROLE_CEO);
        $this->actingAs($ceo)->post(route('users.store'), [
            'name' => 'Tola', 'email' => 'tola@example.com', 'password' => 'Secret123!', 'password_confirmation' => 'Secret123!',
            'role' => Access::ROLE_LOGISTICS,
        ])->assertSessionHasNoErrors();
        $this->assertTrue(User::where('email', 'tola@example.com')->sole()->hasRole(Access::ROLE_LOGISTICS));

        $this->put(route('users.update', $ceo), ['name' => $ceo->name, 'email' => $ceo->email, 'role' => Access::ROLE_PUNCHER])
            ->assertSessionHas('error');
        $this->assertTrue($ceo->fresh()->hasRole(Access::ROLE_CEO));
    }

    public function test_install_manifest_is_only_linked_for_signed_in_staff(): void
    {
        $this->get(route('login'))->assertDontSee('rel="manifest"', false);
        $this->actingAs($this->staff(Access::ROLE_PUNCHER))->get(route('dashboard'))->assertSee('rel="manifest"', false);
    }
}
