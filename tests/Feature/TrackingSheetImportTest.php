<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Modules\CRM\Entities\Client;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Services\TrackingSheetImporter;
use Tests\TestCase;

class TrackingSheetImportTest extends TestCase
{
    use RefreshDatabase;

    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = base_path('tests/fixtures/tracking-sheet.csv');
    }

    public function test_dry_run_reports_without_saving(): void
    {
        $report = app(TrackingSheetImporter::class)->import($this->fixture, dryRun: true);

        $this->assertSame(4, $report['rows']);
        $this->assertSame(4, $report['created']);
        $this->assertNotEmpty($report['fixes']);
        $this->assertSame(0, Shipment::count());
    }

    public function test_import_normalizes_spelling_and_builds_containers(): void
    {
        $report = app(TrackingSheetImporter::class)->import($this->fixture);

        $this->assertEmpty($report['errors']);
        $this->assertSame(4, Shipment::count());
        $this->assertSame(['ACCORD', 'TUCSON', 'RX 350', 'CAMRY'], Shipment::orderBy('id')->pluck('vehicle_model')->all());
        $this->assertSame(['HONDA', 'HYUNDAI', 'LEXUS', 'TOYOTA'], Shipment::orderBy('id')->pluck('vehicle_make')->all());

        // Duplicate tracking ID gets a fresh one instead of colliding.
        $this->assertSame(4, Shipment::distinct()->count('tracking_id'));

        // Year typo 0206 is corrected to 2026.
        $this->assertSame('2026-09-05', Shipment::where('vehicle_model', 'CAMRY')->first()->date_added->toDateString());

        $container = Container::with('shippingLine')->sole();
        $this->assertSame('HAPAG-LLOYD', $container->shippingLine->name);
        $this->assertSame(2, $container->shipments()->count());
        $this->assertSame(Shipment::STATUS_SAILED, $container->shipments()->first()->status);

        $this->assertSame(2, Client::count());
    }

    public function test_reimport_updates_by_vin_instead_of_duplicating(): void
    {
        app(TrackingSheetImporter::class)->import($this->fixture);
        $report = app(TrackingSheetImporter::class)->import($this->fixture);

        $this->assertGreaterThanOrEqual(1, $report['updated']);
        $this->assertSame(1, Shipment::where('vin', '1HGCM82633A004352')->count());
        $this->assertSame(4, Shipment::count());
    }

    public function test_upload_endpoint_previews_then_imports(): void
    {
        $this->actingAs(User::factory()->create());
        $file = fn () => UploadedFile::fake()->createWithContent('sheet.csv', file_get_contents($this->fixture));

        $this->post(route('operations.shipments.import'), ['file' => $file()])
            ->assertSessionHas('importReport');
        $this->assertSame(0, Shipment::count());

        $this->post(route('operations.shipments.import'), ['file' => $file(), 'confirm' => true]);
        $this->assertSame(4, Shipment::count());
    }
}
