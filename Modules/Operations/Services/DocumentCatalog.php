<?php

namespace Modules\Operations\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Shipment;

/**
 * The kinds of paper each stage produces, which are expected (shown as missing
 * until uploaded) and which show prices (hidden from roles without money.view).
 */
final class DocumentCatalog
{
    /** type => [label, stage, sensitive, expected] */
    public const VEHICLE = [
        'purchase_receipt' => ['Auction / purchase receipt', Shipment::STATUS_PURCHASED, true, true],
        'title' => ['Title / ownership document', Shipment::STATUS_PURCHASED, false, true],
        'bill_of_sale' => ['Bill of sale', Shipment::STATUS_PURCHASED, true, false],
        'gate_pass' => ['Gate pass / release form', Shipment::STATUS_PICKED_UP, false, false],
        'tow_receipt' => ['Towing receipt', Shipment::STATUS_PICKED_UP, true, false],
        'vehicle_photos' => ['Vehicle photos', Shipment::STATUS_AT_YARD, false, false],
        'export_declaration' => ['Export declaration (CBSA)', Shipment::STATUS_LOADED, false, false],
        'customs_clearance' => ['Customs clearance papers', Shipment::STATUS_CLEARED, false, true],
        'proof_of_delivery' => ['Proof of delivery', Shipment::STATUS_DELIVERED, false, true],
        'other' => ['Other document', null, false, false],
    ];

    public const CONTAINER = [
        'booking_confirmation' => ['Booking confirmation', Container::STATUS_PLANNED, false, false],
        'dock_receipt' => ['Dock receipt', Container::STATUS_LOADED, false, true],
        'loading_photos' => ['Loading photos', Container::STATUS_LOADED, false, false],
        'bill_of_lading' => ['Bill of Lading', Container::STATUS_SAILED, false, true],
        'freight_invoice' => ['Freight invoice', Container::STATUS_SAILED, true, false],
        'arrival_notice' => ['Arrival notice', Container::STATUS_ARRIVED, false, true],
        'release_order' => ['Release / delivery order', Container::STATUS_RELEASED, false, false],
        'other' => ['Other document', null, false, false],
    ];

    /** General cargo clearing jobs (a Shipment with cargo_type = general_cargo). Stages match Shipment::CLEARING_STATUSES. */
    public const CARGO = [
        'bill_of_lading' => ['Bill of Lading', 'awaiting_docs', false, true],
        'commercial_invoice' => ['Commercial invoice', 'awaiting_docs', true, true],
        'packing_list' => ['Packing list', 'awaiting_docs', false, true],
        'form_m' => ['Form M', 'pre_arrival', false, true],
        'paar' => ['PAAR', 'pre_arrival', false, true],
        'soncap' => ['SONCAP certificate', 'pre_arrival', false, false],
        'nafdac_permit' => ['NAFDAC permit', 'pre_arrival', false, false],
        'duty_assessment' => ['Duty assessment notice', 'duty_assessed', true, true],
        'duty_receipt' => ['Duty payment receipt', 'duty_paid', true, true],
        'customs_release' => ['Customs release', 'released', false, true],
        'terminal_receipt' => ['Terminal receipt', 'released', true, false],
        'delivery_order' => ['Shipping line delivery order', 'released', false, true],
        'waybill_pod' => ['Waybill / proof of delivery', 'delivered', false, true],
        'empty_return' => ['Empty container return receipt', 'empty_returned', false, true],
        'other' => ['Other document', null, false, false],
    ];

    public const CARGO_ORDER = [
        'awaiting_docs', 'pre_arrival', 'arrived', 'duty_assessed', 'duty_paid', 'examination',
        'released', 'delivered', 'empty_returned',
    ];

    public const VEHICLE_ORDER = [
        Shipment::STATUS_ADDED, Shipment::STATUS_PURCHASED, Shipment::STATUS_PICKED_UP, Shipment::STATUS_AT_YARD,
        Shipment::STATUS_LOADED, Shipment::STATUS_SAILED, Shipment::STATUS_ARRIVED, Shipment::STATUS_OFFLOADED,
        Shipment::STATUS_CLEARED, Shipment::STATUS_DELIVERED,
    ];

    public const CONTAINER_ORDER = [
        Container::STATUS_PLANNED, Container::STATUS_LOADED, Container::STATUS_SAILED,
        Container::STATUS_ARRIVED, Container::STATUS_OFFLOADED, Container::STATUS_RELEASED,
    ];

    public static function typesFor(string $kind): array
    {
        return match ($kind) {
            'container' => self::CONTAINER,
            'cargo' => self::CARGO,
            default => self::VEHICLE,
        };
    }

    /** 'vehicle', 'cargo' or 'container' for a record. */
    public static function kindOf(Shipment|Container $owner): string
    {
        if ($owner instanceof Container) {
            return 'container';
        }

        return ($owner->cargo_type ?? 'vehicle') === 'general_cargo' ? 'cargo' : 'vehicle';
    }

    private static function orderFor(string $kind): array
    {
        return match ($kind) {
            'container' => self::CONTAINER_ORDER,
            'cargo' => self::CARGO_ORDER,
            default => self::VEHICLE_ORDER,
        };
    }

    private static function statusLabels(string $kind): array
    {
        return match ($kind) {
            'container' => Container::getStatuses(),
            'cargo' => defined(Shipment::class . '::CLEARING_STATUSES') ? Shipment::CLEARING_STATUSES : [],
            default => Shipment::getStatuses(),
        };
    }

    public static function label(string $type): string
    {
        return self::VEHICLE[$type][0] ?? self::CONTAINER[$type][0] ?? self::CARGO[$type][0] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /** Picklist for the upload form, without the price-bearing types for roles that can't see money. */
    public static function options(string $kind, ?User $user): array
    {
        $seesMoney = (bool) $user?->can('money.view');
        $statuses = self::statusLabels($kind);

        return collect(self::typesFor($kind))
            ->filter(fn ($def) => $seesMoney || !$def[2])
            ->map(fn ($def, $type) => [
                'value' => $type,
                'label' => $def[0],
                'stage' => $def[1],
                'stage_label' => $def[1] ? ($statuses[$def[1]] ?? ucfirst(str_replace('_', ' ', $def[1]))) : null,
                'sensitive' => $def[2],
            ])->values()->all();
    }

    /**
     * Expected papers the record has reached the stage for but nobody uploaded yet.
     */
    public static function missing(string $kind, ?string $status, Collection $documents, ?User $user): array
    {
        $order = self::orderFor($kind);
        $reached = array_search($status, $order, true);
        if ($reached === false) {
            return [];
        }
        $seesMoney = (bool) $user?->can('money.view');
        $have = $documents->pluck('type')->unique()->all();

        return collect(self::typesFor($kind))
            ->filter(fn ($def, $type) => $def[3] && $def[1] !== null
                && array_search($def[1], $order, true) <= $reached
                && !in_array($type, $have, true)
                && ($seesMoney || !$def[2]))
            ->map(fn ($def, $type) => ['type' => $type, 'label' => $def[0]])
            ->values()->all();
    }

    /** Documents for the panel, filtered to what this user may see. */
    public static function visible(Collection $documents, ?User $user): Collection
    {
        return $documents->filter(fn ($d) => $d->visibleTo($user))->values();
    }
}
