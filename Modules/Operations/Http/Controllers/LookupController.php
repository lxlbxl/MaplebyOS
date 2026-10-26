<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\Operations\Entities\Port;
use Modules\Operations\Entities\PurchasePlatform;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Entities\VehicleMake;
use Modules\Operations\Entities\VehicleModel;
use Modules\Operations\Services\SmartMatcher;
use Modules\Operations\Services\VinDecoder;

/**
 * Smart-suggestion endpoints and the catalog editor.
 */
class LookupController extends Controller
{
    private const MODELS = [
        'makes' => VehicleMake::class,
        'models' => VehicleModel::class,
        'shipping_lines' => ShippingLine::class,
        'platforms' => PurchasePlatform::class,
        'ports' => Port::class,
    ];

    public function suggest(Request $request, string $type, SmartMatcher $matcher)
    {
        abort_unless(in_array($type, SmartMatcher::TYPES, true), 404);

        return response()->json([
            'suggestions' => $matcher->suggest($type, $request->input('q'), $this->context($request), (int) $request->input('limit', 8)),
            'resolved' => $request->filled('q') ? $matcher->resolve($type, $request->input('q'), $this->context($request)) : null,
        ]);
    }

    public function catalog()
    {
        return Inertia::render('Operations/Catalog/Index', [
            'makes' => VehicleMake::with('models:id,vehicle_make_id,name,aliases')->orderBy('name')->get(['id', 'name', 'aliases']),
            'shippingLines' => ShippingLine::orderBy('name')->get(),
            'platforms' => PurchasePlatform::orderBy('name')->get(),
            'ports' => Port::orderBy('kind')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type, SmartMatcher $matcher)
    {
        $class = self::MODELS[$type] ?? abort(404);
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'aliases' => 'nullable',
            'make' => [Rule::requiredIf($type === 'models'), 'nullable', 'string'],
            'country' => 'nullable|string|max:60',
            'kind' => 'nullable|in:origin,destination',
            'scac' => 'nullable|string|max:8',
            'tracking_url' => 'nullable|url|max:255',
            'container_prefixes' => 'nullable',
            'force' => 'nullable|boolean',
        ]);

        $context = [];
        if ($type === 'models') {
            $make = VehicleMake::where('name', $matcher->resolve('makes', $data['make'])['value'])->first();
            if (!$make) {
                throw ValidationException::withMessages(['make' => 'Choose an existing make first.']);
            }
            $context = ['make' => $make->name];
        }

        // Stop near-duplicates: "HYUDAI" when HYUNDAI exists.
        $existing = $matcher->resolve($type, $data['name'], $context);
        if ($existing['value'] && !$request->boolean('force')) {
            throw ValidationException::withMessages([
                'name' => $existing['confidence'] === 'typo'
                    ? "\"{$data['name']}\" looks like a misspelling of {$existing['value']}. Use that, or tick \"it's really different\"."
                    : "{$existing['value']} already exists.",
            ]);
        }

        $entry = new $class();
        $entry->name = $data['name'];
        $entry->aliases = $data['aliases'] ?? [];
        if ($type === 'models') {
            $entry->vehicle_make_id = $make->id;
        }
        foreach (['country', 'kind', 'scac', 'tracking_url'] as $field) {
            if (array_key_exists($field, $data) && in_array($field, $entry->getFillable(), true)) {
                $entry->{$field} = $data[$field];
            }
        }
        if ($type === 'shipping_lines') {
            $entry->container_prefixes = $this->list($data['container_prefixes'] ?? []);
        }
        $entry->save();

        if ($request->wantsJson()) {
            return response()->json(['entry' => $entry], 201);
        }

        return redirect()->back()->with('success', "{$entry->name} added.");
    }

    public function update(Request $request, string $type, int $id)
    {
        $class = self::MODELS[$type] ?? abort(404);
        $entry = $class::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'aliases' => 'nullable',
            'country' => 'nullable|string|max:60',
            'kind' => 'nullable|in:origin,destination',
            'scac' => 'nullable|string|max:8',
            'tracking_url' => 'nullable|string|max:255',
            'container_prefixes' => 'nullable',
        ]);

        $oldName = $entry->name;
        $entry->name = $data['name'];
        $entry->aliases = $data['aliases'] ?? [];
        foreach (['country', 'kind', 'scac', 'tracking_url'] as $field) {
            if (array_key_exists($field, $data) && in_array($field, $entry->getFillable(), true)) {
                $entry->{$field} = $data[$field];
            }
        }
        if ($type === 'shipping_lines') {
            $entry->container_prefixes = $this->list($data['container_prefixes'] ?? []);
        }
        $entry->save();

        // Renaming a canonical value renames it on every vehicle too.
        if ($oldName !== $entry->name) {
            $column = ['makes' => 'vehicle_make', 'models' => 'vehicle_model', 'platforms' => 'purchase_platform', 'ports' => 'destination_city'][$type] ?? null;
            if ($column) {
                Shipment::where($column, $oldName)->update([$column => $entry->name]);
            }
        }

        return redirect()->back()->with('success', "{$entry->name} saved.");
    }

    public function destroy(string $type, int $id)
    {
        $class = self::MODELS[$type] ?? abort(404);
        $class::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Removed from the catalog.');
    }

    /**
     * Offline VIN check plus "already in the system" warning.
     */
    public function vin(Request $request, string $vin)
    {
        $decoded = VinDecoder::decode($vin);
        $existing = $decoded['vin'] !== ''
            ? Shipment::where('vin', $decoded['vin'])->when($request->filled('ignore'), fn ($q) => $q->where('id', '!=', $request->input('ignore')))
                ->first(['id', 'tracking_id', 'title', 'status'])
            : null;

        return response()->json($decoded + ['existing' => $existing]);
    }

    private function context(Request $request): array
    {
        return array_filter($request->only(['make', 'kind']));
    }

    private function list($value): array
    {
        return collect(is_string($value) ? explode(',', $value) : (array) $value)
            ->map(fn ($v) => strtoupper(trim((string) $v)))->filter()->unique()->values()->all();
    }
}
