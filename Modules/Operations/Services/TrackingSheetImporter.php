<?php

namespace Modules\Operations\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CRM\Entities\Client;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShippingLine;

/**
 * Imports the "MAPLEBY TRACKING SYSTEM - SHIPMENT" sheet (CSV export).
 *
 * Every value goes through the same smart matching as typed input, so makes,
 * models and shipping lines land on canonical names. Problems that can be fixed
 * safely are fixed and reported; anything doubtful is kept and flagged.
 * Re-running the import updates cars matched by VIN instead of duplicating them.
 */
class TrackingSheetImporter
{
    public const COLUMNS = [
        'tracking_id' => ['TRACKING ID'],
        'date_added' => ['DATE ADDED'],
        'customer' => ['CUSTOMER NAME', 'CUSTOMER'],
        'vin' => ['VIN'],
        'city' => ['CITY'],
        'make' => ['CAR MAKE', 'MAKE'],
        'model' => ['CAR MODEL', 'MODEL'],
        'year' => ['YEAR'],
        'loaded_on' => ['DATE LOADED'],
        'container_series' => ['CONTAINER SERIES'],
        'container_number' => ['CONTAINER NUMBER'],
        'shipping_line' => ['SHIPPING LINE'],
        'offloaded_on' => ['DATE OFFLOADED'],
        'status' => ['STATUS'],
        'purchase_platform' => ['PURCHASE PLATFORM'],
        'note' => ['NOTE', 'NOTES'],
    ];

    private const STATUS_MAP = [
        'ADDED' => Shipment::STATUS_ADDED,
        'PURCHASED' => Shipment::STATUS_PURCHASED,
        'AT YARD' => Shipment::STATUS_AT_YARD,
        'LOADED' => Shipment::STATUS_LOADED,
        'SAILED' => Shipment::STATUS_SAILED,
        'SHIPPED' => Shipment::STATUS_SAILED,
        'IN TRANSIT' => Shipment::STATUS_SAILED,
        'ARRIVED' => Shipment::STATUS_ARRIVED,
        'OFFLOADED' => Shipment::STATUS_OFFLOADED,
        'CLEARED' => Shipment::STATUS_CLEARED,
        'DELIVERED' => Shipment::STATUS_DELIVERED,
    ];

    private array $report;

    /** @var array<string, int> tracking IDs already used by earlier rows of this file */
    private array $seenTrackingIds = [];

    /** @var array<string, int> well-formed tracking IDs present anywhere in the file */
    private array $reservedTrackingIds = [];

    /** @var array<int, bool> shipments already matched by an earlier row of this file */
    private array $matchedIds = [];

    /** @var array<string, Container> */
    private array $containers = [];

    public function __construct(private SmartMatcher $matcher, private TrackingIdGenerator $ids)
    {
    }

    /**
     * @return array report with counts, fixes, warnings and per-row results
     */
    public function import(string $path, bool $dryRun = false): array
    {
        $this->report = [
            'dry_run' => $dryRun,
            'rows' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'containers_created' => 0,
            'fixes' => [],
            'warnings' => [],
            'errors' => [],
        ];
        $this->seenTrackingIds = [];
        $this->matchedIds = [];
        $this->containers = [];

        $rows = $this->readRows($path);
        $this->reservedTrackingIds = collect($rows)->pluck('tracking_id')
            ->map(fn ($id) => strtoupper(trim((string) $id)))
            ->filter(fn ($id) => TrackingIdGenerator::isWellFormed($id))
            ->flip()->map(fn () => 1)->all();

        DB::beginTransaction();
        try {
            foreach ($rows as $line => $row) {
                $this->report['rows']++;
                $this->importRow($row, $line);
            }
            $dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->report;
    }

    /**
     * Read the CSV, tolerating headers that span several lines ("DATE \nLOADED").
     *
     * @return array<int, array<string, string>> keyed by spreadsheet row number
     */
    public function readRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open {$path}");
        }

        $header = fgetcsv($handle, escape: '');
        if (!$header) {
            return [];
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        $map = [];
        foreach ($header as $index => $name) {
            $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
            foreach (self::COLUMNS as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$index] = $field;
                }
            }
        }

        if (!in_array('vin', $map, true) || !in_array('tracking_id', $map, true)) {
            throw new \RuntimeException('This does not look like the tracking sheet: TRACKING ID and VIN columns are required.');
        }

        $rows = [];
        $line = 1;
        while (($values = fgetcsv($handle, escape: '')) !== false) {
            $line++;
            $row = [];
            foreach ($map as $index => $field) {
                $row[$field] = trim(preg_replace('/\s+/', ' ', (string) ($values[$index] ?? '')));
            }
            if (implode('', $row) === '') {
                continue;
            }
            $rows[$line] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function importRow(array $row, int $line): void
    {
        $ref = $row['tracking_id'] ?: "row {$line}";

        // Dates
        $dateAdded = $this->parseDate($row['date_added'] ?? '', $ref, 'date added');
        $loadedOn = $this->parseDate($row['loaded_on'] ?? '', $ref, 'date loaded', $dateAdded);
        $offloadedOn = $this->parseDate($row['offloaded_on'] ?? '', $ref, 'date offloaded', $loadedOn ?? $dateAdded);
        $dateAdded ??= $loadedOn ?? Carbon::today();

        if ($loadedOn && $loadedOn->lt($dateAdded)) {
            $this->warn($ref, "Date loaded ({$loadedOn->toDateString()}) is before date added ({$dateAdded->toDateString()}).");
        }
        if ($offloadedOn && $loadedOn && $offloadedOn->lt($loadedOn)) {
            $this->warn($ref, "Date offloaded ({$offloadedOn->toDateString()}) is before date loaded.");
        }

        // VIN
        $vin = null;
        if (($row['vin'] ?? '') !== '') {
            $decoded = VinDecoder::decode($row['vin']);
            $vin = $decoded['vin'];
            foreach ($decoded['errors'] as $error) {
                $this->warn($ref, "VIN {$vin}: {$error}");
            }
        } else {
            $this->warn($ref, 'No VIN.');
        }

        // Make / model / year
        [$make, $model] = $this->resolveVehicle($row['make'] ?? '', $row['model'] ?? '', $ref);
        $year = $this->resolveYear($row['year'] ?? '', $vin, $ref);

        // Existing car (re-import) is matched by VIN, or for cars without one,
        // by tracking ID and then by the same vehicle added on the same day.
        $shipment = $this->findExisting($vin, $row['tracking_id'] ?? '', $make, $model, $year, $dateAdded);

        // Tracking ID: keep it when free, otherwise issue a new one.
        $trackingId = $this->resolveTrackingId($row['tracking_id'] ?? '', $shipment, $dateAdded, $ref);

        // Container + line
        $container = $this->resolveContainer($row, $loadedOn, $offloadedOn, $ref);

        // Status
        $status = $this->resolveStatus($row['status'] ?? '', $ref, $container !== null);

        // Customer
        $clientId = null;
        if (($row['customer'] ?? '') !== '') {
            $clientId = $this->resolveCustomer($row['customer'], $ref);
        }

        $platform = null;
        if (($row['purchase_platform'] ?? '') !== '') {
            $platform = $this->resolveValue('platforms', $row['purchase_platform'], $ref, 'purchase platform');
        }

        $city = null;
        if (($row['city'] ?? '') !== '') {
            $city = $this->resolveValue('ports', $row['city'], $ref, 'city');
        }

        $attributes = array_filter([
            'tracking_id' => $trackingId,
            'vin' => $vin,
            'vehicle_make' => $make,
            'vehicle_model' => $model,
            'vehicle_year' => $year,
            'date_added' => $dateAdded,
            'loaded_on' => $loadedOn,
            'offloaded_on' => $offloadedOn,
            'status' => $status,
            'container_id' => $container?->id,
            'client_id' => $clientId,
            'destination_city' => $city,
            'destination' => $city,
            'purchase_platform' => $platform,
            'service_type' => 'Container shipping',
            'description' => $row['note'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        if ($shipment) {
            if ($shipment->trashed()) {
                $shipment->restore();
            }
            $shipment->fill($attributes)->save();
            $this->report['updated']++;
        } else {
            $shipment = Shipment::create($attributes);
            $this->report['created']++;
            $this->recordHistory($shipment);
        }

        $this->matchedIds[$shipment->id] = true;

        if ($container && !$shipment->container_slot) {
            $shipment->container_slot = $container->shipments()->where('id', '!=', $shipment->id)->count() + 1;
            $shipment->save();
            if ($shipment->container_slot > $container->capacity) {
                $this->warn($ref, "Container {$container->container_number} now holds {$shipment->container_slot} cars (capacity {$container->capacity}). Check whether this is a later voyage or a data entry error.");
            }
        }
    }

    private function recordHistory(Shipment $shipment): void
    {
        $events = [[Shipment::STATUS_ADDED, $shipment->date_added]];
        if ($shipment->loaded_on) {
            $events[] = [Shipment::STATUS_LOADED, $shipment->loaded_on];
        }
        if ($shipment->offloaded_on) {
            $events[] = [Shipment::STATUS_OFFLOADED, $shipment->offloaded_on];
        }
        if ($shipment->status === Shipment::STATUS_DELIVERED) {
            $events[] = [Shipment::STATUS_DELIVERED, $shipment->delivered_on ?? $shipment->offloaded_on ?? $shipment->loaded_on ?? $shipment->date_added];
        }

        foreach ($events as [$status, $date]) {
            $shipment->milestones()->create([
                'status' => $status,
                'occurred_on' => $date,
                'note' => 'Imported from tracking sheet',
                'container_id' => $shipment->container_id,
            ]);
        }
    }

    private function resolveVehicle(string $rawMake, string $rawModel, string $ref): array
    {
        $make = null;
        if ($rawMake !== '') {
            $make = $this->resolveValue('makes', $rawMake, $ref, 'make');
        }

        $model = null;
        $context = $make ? ['make' => $make] : [];

        if ($rawModel !== '' && $make && SmartMatcher::key($rawModel) !== '' && $this->matcher->resolve('makes', $rawModel)['value'] === $make) {
            // e.g. make MERCEDES, model BENZ: the "model" is really the make.
            $this->warn($ref, "Model \"{$rawModel}\" is a make name; model left blank.");
            $rawModel = '';
        }

        if ($rawModel === '' && $make && SmartMatcher::key($rawMake) !== SmartMatcher::key($make)) {
            // e.g. make GWAGON -> MERCEDES-BENZ, the model is hidden in the make column.
            $hidden = $this->matcher->resolve('models', $rawMake, $context);
            if (in_array($hidden['confidence'], ['exact', 'alias'], true)) {
                $this->fix($ref, "Model taken from make column: \"{$rawMake}\" -> {$make} {$hidden['value']}.");

                return [$make, $hidden['value']];
            }
        }

        if ($rawModel !== '') {
            $model = $make
                ? $this->resolveValue('models', $rawModel, $ref, 'model', $context)
                : strtoupper($rawModel);
        }

        return [$make, $model];
    }

    private function resolveValue(string $type, string $raw, string $ref, string $label, array $context = []): string
    {
        $result = $this->matcher->resolve($type, $raw, $context);
        $canonical = $result['value'];

        if ($canonical === null) {
            $hint = $result['suggestions'] ? ' Closest: ' . implode(', ', array_column($result['suggestions'], 'value')) . '.' : '';
            $this->warn($ref, "Unknown {$label} \"{$raw}\" kept as typed.{$hint}");

            return strtoupper(trim($raw));
        }

        if (SmartMatcher::key($raw) !== SmartMatcher::key($canonical) || trim($raw) !== $raw) {
            $this->fix($ref, ucfirst($label) . " \"{$raw}\" -> {$canonical}.");
        }

        return $canonical;
    }

    private function resolveYear(string $raw, ?string $vin, string $ref): ?int
    {
        $year = ctype_digit($raw) ? (int) $raw : null;
        $vinYear = ($vin && strlen($vin) === 17) ? VinDecoder::modelYear($vin) : null;

        if ($vinYear && $year && $vinYear !== $year) {
            if (VinDecoder::checkDigitMatches($vin)) {
                $this->fix($ref, "Year {$year} -> {$vinYear} (decoded from VIN).");

                return $vinYear;
            }
            $this->warn($ref, "Year {$year} differs from the VIN ({$vinYear}), but the VIN's check digit fails, so the year was kept.");
        }

        return $year ?? $vinYear;
    }

    private function findExisting(?string $vin, string $rawTrackingId, ?string $make, ?string $model, ?int $year, Carbon $dateAdded): ?Shipment
    {
        if ($vin) {
            return Shipment::withTrashed()->where('vin', $vin)->first();
        }

        $untouched = fn () => Shipment::withTrashed()->whereNull('vin')->whereNotIn('id', array_keys($this->matchedIds));

        if ($rawTrackingId !== '' && !isset($this->seenTrackingIds[strtoupper($rawTrackingId)])) {
            $byId = $untouched()->where('tracking_id', strtoupper($rawTrackingId))->first();
            if ($byId) {
                return $byId;
            }
        }

        return $untouched()
            ->whereDate('date_added', $dateAdded->toDateString())
            ->where('vehicle_make', $make)
            ->where('vehicle_model', $model)
            ->where('vehicle_year', $year)
            ->first();
    }

    private function resolveTrackingId(string $raw, ?Shipment $existing, Carbon $dateAdded, string $ref): string
    {
        $raw = strtoupper(trim($raw));

        if ($existing && $existing->tracking_id && ($raw === '' || $existing->tracking_id === $raw || isset($this->seenTrackingIds[$raw]))) {
            $this->seenTrackingIds[$existing->tracking_id] = 1;

            return $existing->tracking_id;
        }

        $valid = TrackingIdGenerator::isWellFormed($raw);
        if ($raw !== '' && !$valid) {
            $this->warn($ref, "Tracking ID \"{$raw}\" is not in the MAPYY-MM-NNN format; a new one was issued.");
        }

        if ($valid && substr($raw, 3, 2) !== $dateAdded->format('y')) {
            $this->warn($ref, "Tracking ID {$raw} does not match the year it was added ({$dateAdded->format('Y')}).");
            $valid = false;
        }

        $takenByOther = $valid && Shipment::withTrashed()->where('tracking_id', $raw)
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))->exists();

        if ($valid && !isset($this->seenTrackingIds[$raw]) && !$takenByOther) {
            $this->seenTrackingIds[$raw] = 1;

            return $raw;
        }

        $new = $this->nextFreeTrackingId($dateAdded);
        $this->seenTrackingIds[$new] = 1;

        if ($raw !== '') {
            $this->fix($ref, ($valid ? 'Duplicate tracking ID' : 'Tracking ID') . " {$raw} -> {$new}.");
        }

        return $new;
    }

    /**
     * Next number for the month that is not in the database and not used by any row of this file.
     */
    private function nextFreeTrackingId(Carbon $date): string
    {
        $prefix = $this->ids->prefixFor($date);
        $used = collect(array_keys($this->reservedTrackingIds + $this->seenTrackingIds))
            ->filter(fn ($id) => str_starts_with($id, $prefix))
            ->map(fn ($id) => (int) substr($id, strlen($prefix)));
        $fromDb = (int) substr($this->ids->next($date), strlen($prefix)) - 1;
        $next = max($used->max() ?? 0, $fromDb) + 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function resolveContainer(array $row, ?Carbon $loadedOn, ?Carbon $offloadedOn, string $ref): ?Container
    {
        $number = ContainerNumber::clean($row['container_number'] ?? '');
        if (!$number) {
            if (($row['container_series'] ?? '') !== '') {
                $this->warn($ref, "Container series \"{$row['container_series']}\" given without a container number; ignored.");
            }

            return null;
        }

        if (!ContainerNumber::isValid($number)) {
            $this->warn($ref, "Container number {$number} fails the ISO 6346 check digit.");
        }

        $lineName = ($row['shipping_line'] ?? '') !== ''
            ? $this->resolveValue('shipping_lines', $row['shipping_line'], $ref, 'shipping line')
            : null;
        $line = $lineName ? ShippingLine::where('name', $lineName)->first() : ShippingLine::guessFromContainerNumber($number);

        // A container number seen again more than 3 weeks later is a separate trip.
        $key = $number;
        $container = $this->containers[$key] ?? null;
        if ($container && $loadedOn && $container->loaded_on && abs($container->loaded_on->diffInDays($loadedOn)) > 21) {
            $container = null;
            $key = $number . '@' . $loadedOn->toDateString();
        }

        $container ??= Container::where('container_number', $number)
            ->when($loadedOn, fn ($q) => $q->whereBetween('loaded_on', [$loadedOn->copy()->subDays(21), $loadedOn->copy()->addDays(21)]))
            ->first();

        if (!$container) {
            $container = Container::create([
                'container_number' => $number,
                'shipping_line_id' => $line?->id,
                'size_type' => ($row['container_series'] ?? '') !== '' && preg_match('/^(20|40|45)/', $row['container_series']) ? strtoupper($row['container_series']) : null,
                'status' => Container::STATUS_PLANNED,
                'capacity' => 4,
                'loaded_on' => $loadedOn,
                'port_of_discharge' => 'LAGOS',
            ]);
            $this->report['containers_created']++;
        }

        $changes = [];
        if ($loadedOn && (!$container->loaded_on || $loadedOn->lt($container->loaded_on))) {
            $changes['loaded_on'] = $loadedOn;
        }
        if ($offloadedOn && !$container->offloaded_on) {
            $changes['offloaded_on'] = $offloadedOn;
        }
        if (!$container->shipping_line_id && $line) {
            $changes['shipping_line_id'] = $line->id;
        }
        $status = $container->offloaded_on || isset($changes['offloaded_on']) ? Container::STATUS_OFFLOADED : (($container->loaded_on || isset($changes['loaded_on'])) ? Container::STATUS_LOADED : $container->status);
        if ($status !== $container->status) {
            $changes['status'] = $status;
        }
        if ($changes) {
            $container->fill($changes)->save();
        }

        return $this->containers[$key] = $container;
    }

    private function resolveStatus(string $raw, string $ref, bool $hasContainer): string
    {
        $key = strtoupper(trim($raw));
        if ($key === '') {
            return $hasContainer ? Shipment::STATUS_LOADED : Shipment::STATUS_ADDED;
        }

        if (isset(self::STATUS_MAP[$key])) {
            return self::STATUS_MAP[$key];
        }

        foreach (self::STATUS_MAP as $label => $status) {
            if (SmartMatcher::distance($key, $label) <= 2) {
                $this->fix($ref, "Status \"{$raw}\" -> {$label}.");

                return $status;
            }
        }

        $this->warn($ref, "Unknown status \"{$raw}\"; set to Added.");

        return Shipment::STATUS_ADDED;
    }

    private function resolveCustomer(string $name, string $ref): int
    {
        $result = $this->matcher->resolve('customers', $name);
        if ($result['match'] && in_array($result['confidence'], ['exact', 'alias'], true)) {
            return (int) $result['match']['id'];
        }
        if ($result['match'] && $result['confidence'] === 'typo') {
            $this->fix($ref, "Customer \"{$name}\" matched to existing customer {$result['match']['label']}.");

            return (int) $result['match']['id'];
        }

        $client = Client::create(['contact_person' => Str::title($name), 'source' => 'tracking_sheet_import', 'type' => 'buyer']);
        $this->matcher = app(SmartMatcher::class); // refresh cached customer list

        return $client->id;
    }

    /**
     * Parse the sheet's mixed date formats. Years like 0202 or 0206 are typos for
     * the year of the reference date and are corrected.
     */
    private function parseDate(string $raw, string $ref, string $label, ?Carbon $reference = null): ?Carbon
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $date = null;
        foreach (['j-M-Y', 'd-M-Y', 'j F Y', 'j M Y', 'Y-m-d', 'n/j/Y', 'd/m/Y', 'j-M-y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat('!' . $format, $raw);
                if ($parsed && $parsed->format($format) !== false) {
                    $date = $parsed;
                    break;
                }
            } catch (\Throwable) {
                // try the next format
            }
        }

        if (!$date) {
            $this->warn($ref, "Could not read {$label} \"{$raw}\"; left blank.");

            return null;
        }

        if ($date->year < 2000) {
            $fixedYear = $reference?->year ?? (int) date('Y');
            $fixed = $date->copy()->year($fixedYear);
            if ($reference && $fixed->lt($reference->copy()->subMonths(1))) {
                $fixed->addYear();
            }
            $this->fix($ref, ucfirst($label) . " \"{$raw}\" -> {$fixed->toDateString()} (year typo).");

            return $fixed;
        }

        return $date;
    }

    private function fix(string $ref, string $message): void
    {
        $this->report['fixes'][] = "{$ref}: {$message}";
    }

    private function warn(string $ref, string $message): void
    {
        $this->report['warnings'][] = "{$ref}: {$message}";
    }
}
