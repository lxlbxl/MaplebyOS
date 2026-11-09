<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Services\TrackingIdGenerator;
use Modules\Operations\Services\VinDecoder;

/**
 * One unit of work moving through a pipeline. Either a vehicle (one row of the
 * tracking sheet) or a general cargo clearing job (one Bill of Lading of a
 * client's goods, in one or more containers). See cargo_type.
 */
class Shipment extends Model
{
    use SoftDeletes, \App\Models\Concerns\HidesRestrictedFields;

    protected static function restrictedFields(): array
    {
        return ['money.view' => \App\Support\Access::SHIPMENT_MONEY_FIELDS];
    }

    protected $table = 'shipments';

    protected $fillable = [
        'title',
        'shipment_number',
        'tracking_id',
        'client_id',
        'container_id',
        'container_slot',
        'status',
        'service_type',
        'origin',
        'destination',
        'destination_city',
        'location_notes',
        'estimated_completion',
        'total_value',
        'assigned_to',
        'description',
        'vin',
        'vehicle_year',
        'vehicle_make',
        'vehicle_model',
        'vehicle_trim',
        'vehicle_color',
        'has_keys',
        'tow_required',
        'driver_name',
        'ownership_documents',
        'date_received',
        'date_added',
        'loaded_on',
        'offloaded_on',
        'delivered_on',
        'purchase_platform',
        'lot_number',
        'purchase_date',
        'purchase_price',
        'payment_status',
        'created_by',
        'cargo_type',
        'cargo_description',
        'hs_code',
        'package_count',
        'package_type',
        'gross_weight_kg',
        'volume_cbm',
        'declared_value',
        'declared_currency',
        'consignee',
        'bl_number',
        'form_m_number',
        'paar_number',
        'customs_entry_number',
        'terminal',
        'duty_assessed',
        'eta',
        'arrived_on',
        'duty_assessed_on',
        'duty_paid_on',
        'examined_on',
        'released_on',
        'empty_returned_on',
    ];

    protected $casts = [
        'ownership_documents' => 'array',
        'tow_required' => 'boolean',
        'has_keys' => 'boolean',
        'estimated_completion' => 'date:Y-m-d',
        'date_received' => 'date:Y-m-d',
        'date_added' => 'date:Y-m-d',
        'loaded_on' => 'date:Y-m-d',
        'offloaded_on' => 'date:Y-m-d',
        'delivered_on' => 'date:Y-m-d',
        'purchase_date' => 'date:Y-m-d',
        'total_value' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'eta' => 'date:Y-m-d',
        'arrived_on' => 'date:Y-m-d',
        'duty_assessed_on' => 'date:Y-m-d',
        'duty_paid_on' => 'date:Y-m-d',
        'examined_on' => 'date:Y-m-d',
        'released_on' => 'date:Y-m-d',
        'empty_returned_on' => 'date:Y-m-d',
        'package_count' => 'integer',
        'gross_weight_kg' => 'decimal:2',
        'volume_cbm' => 'decimal:3',
        'declared_value' => 'decimal:2',
        'duty_assessed' => 'decimal:2',
    ];

    protected $appends = ['vehicle_label', 'status_label'];

    const STATUS_ADDED = 'added';
    const STATUS_PURCHASED = 'purchased';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_AT_YARD = 'at_yard';
    const STATUS_LOADED = 'loaded';
    const STATUS_SAILED = 'sailed';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_OFFLOADED = 'offloaded';
    const STATUS_CLEARED = 'cleared';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_ISSUE = 'issue';

    /** What a shipment row carries. */
    const CARGO_VEHICLE = 'vehicle';
    const CARGO_GENERAL = 'general_cargo';
    const CARGO_BOX = 'box';

    public const CARGO_TYPES = [
        self::CARGO_VEHICLE => 'Vehicle',
        self::CARGO_BOX => 'Box / lite cargo (rides in a car container)',
        self::CARGO_GENERAL => 'General cargo (clearing)',
    ];

    /**
     * Boxes ride in car containers, so they share the car pipeline from the
     * yard onward (container status changes cascade to them too).
     */
    public const BOX_STATUSES = [
        self::STATUS_ADDED => 'Booked',
        self::STATUS_AT_YARD => 'Received at yard',
        self::STATUS_LOADED => 'Loaded',
        self::STATUS_SAILED => 'Sailed',
        self::STATUS_ARRIVED => 'Arrived Lagos',
        self::STATUS_OFFLOADED => 'Offloaded',
        self::STATUS_CLEARED => 'Cleared',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_ON_HOLD => 'On hold',
        self::STATUS_ISSUE => 'Issue',
    ];

    /** Clearing pipeline for general cargo arriving in Nigeria. */
    const STATUS_AWAITING_DOCS = 'awaiting_docs';
    const STATUS_PRE_ARRIVAL = 'pre_arrival';
    const STATUS_DUTY_ASSESSED = 'duty_assessed';
    const STATUS_DUTY_PAID = 'duty_paid';
    const STATUS_EXAMINATION = 'examination';
    const STATUS_RELEASED = 'released';
    const STATUS_EMPTY_RETURNED = 'empty_returned';

    public const CLEARING_STATUSES = [
        self::STATUS_AWAITING_DOCS => 'Awaiting documents',
        self::STATUS_PRE_ARRIVAL => 'Form M / PAAR',
        self::STATUS_ARRIVED => 'Arrived port',
        self::STATUS_DUTY_ASSESSED => 'Duty assessed',
        self::STATUS_DUTY_PAID => 'Duty paid',
        self::STATUS_EXAMINATION => 'Examination',
        self::STATUS_RELEASED => 'Customs released',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_EMPTY_RETURNED => 'Empty returned',
        self::STATUS_ON_HOLD => 'On hold',
        self::STATUS_ISSUE => 'Issue',
    ];

    /** Which date column a clearing status fills in when it is reached. */
    public const CLEARING_STATUS_DATE_FIELDS = [
        self::STATUS_ARRIVED => 'arrived_on',
        self::STATUS_DUTY_ASSESSED => 'duty_assessed_on',
        self::STATUS_DUTY_PAID => 'duty_paid_on',
        self::STATUS_EXAMINATION => 'examined_on',
        self::STATUS_RELEASED => 'released_on',
        self::STATUS_DELIVERED => 'delivered_on',
        self::STATUS_EMPTY_RETURNED => 'empty_returned_on',
    ];

    /** Clearing status => [container date column, container status] pushed to every container of the job. */
    public const CLEARING_CONTAINER_CASCADE = [
        self::STATUS_ARRIVED => ['arrived_on', Container::STATUS_ARRIVED],
        self::STATUS_RELEASED => ['released_on', Container::STATUS_RELEASED],
        self::STATUS_EMPTY_RETURNED => ['empty_returned_on', Container::STATUS_RELEASED],
    ];

    /** Tracking ID prefix per cargo type. */
    public const TRACKING_PREFIXES = [
        self::CARGO_VEHICLE => 'MAP',
        self::CARGO_GENERAL => 'CLR',
        self::CARGO_BOX => 'BOX',
    ];

    /**
     * Pipeline order. On hold / issue are side states shown separately.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ADDED => 'Added',
            self::STATUS_PURCHASED => 'Purchased',
            self::STATUS_PICKED_UP => 'Picked up',
            self::STATUS_AT_YARD => 'At yard',
            self::STATUS_LOADED => 'Loaded',
            self::STATUS_SAILED => 'Sailed',
            self::STATUS_ARRIVED => 'Arrived Lagos',
            self::STATUS_OFFLOADED => 'Offloaded',
            self::STATUS_CLEARED => 'Cleared',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_ON_HOLD => 'On hold',
            self::STATUS_ISSUE => 'Issue',
        ];
    }

    /**
     * Which date column a status fills in when it is reached.
     */
    public const STATUS_DATE_FIELDS = [
        self::STATUS_PURCHASED => 'purchase_date',
        self::STATUS_LOADED => 'loaded_on',
        self::STATUS_OFFLOADED => 'offloaded_on',
        self::STATUS_DELIVERED => 'delivered_on',
    ];

    /** The status list for a cargo type (vehicles keep the original pipeline). */
    public static function statusesFor(?string $cargoType): array
    {
        return match ($cargoType) {
            self::CARGO_GENERAL => self::CLEARING_STATUSES,
            self::CARGO_BOX => self::BOX_STATUSES,
            default => self::getStatuses(),
        };
    }

    public function isGeneralCargo(): bool
    {
        return $this->cargo_type === self::CARGO_GENERAL;
    }

    public function isBox(): bool
    {
        return $this->cargo_type === self::CARGO_BOX;
    }

    /** Boxes and clearing jobs are described by their contents, not make/model. */
    public function isCargo(): bool
    {
        return $this->isGeneralCargo() || $this->isBox();
    }

    public function scopeBoxes($query)
    {
        return $query->where('cargo_type', self::CARGO_BOX);
    }

    public function scopeVehicles($query)
    {
        return $query->where('cargo_type', self::CARGO_VEHICLE);
    }

    public function scopeGeneralCargo($query)
    {
        return $query->where('cargo_type', self::CARGO_GENERAL);
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            $shipment->cargo_type ??= self::CARGO_VEHICLE;
            $shipment->date_added ??= Carbon::today();
            $shipment->status ??= $shipment->isGeneralCargo() ? self::STATUS_AWAITING_DOCS : self::STATUS_ADDED;
            if (empty($shipment->tracking_id)) {
                $shipment->tracking_id = app(TrackingIdGenerator::class)->next(
                    Carbon::parse($shipment->date_added),
                    self::TRACKING_PREFIXES[$shipment->cargo_type] ?? TrackingIdGenerator::PREFIX
                );
            }
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = $shipment->tracking_id;
            }
        });

        static::saving(function (Shipment $shipment) {
            if ($shipment->vin !== null) {
                $shipment->vin = VinDecoder::clean($shipment->vin) ?: null;
            }
            if ($shipment->isCargo()) {
                if (empty($shipment->title) || $shipment->isDirty('cargo_description')) {
                    $shipment->title = $shipment->cargo_description ?: ($shipment->title ?: $shipment->tracking_id);
                }
                if ($shipment->bl_number !== null) {
                    $shipment->bl_number = strtoupper(preg_replace('/\s+/', '', $shipment->bl_number)) ?: null;
                }
            } elseif (empty($shipment->title) || $shipment->isDirty(['vehicle_year', 'vehicle_make', 'vehicle_model'])) {
                $shipment->title = $shipment->buildVehicleLabel() ?: ($shipment->title ?: $shipment->tracking_id);
            }
        });
    }

    public function buildVehicleLabel(): string
    {
        return trim(implode(' ', array_filter([$this->vehicle_year, $this->vehicle_make, $this->vehicle_model])));
    }

    /** Statuses before the car goes into a loaded container. */
    public const PRE_LOADING_STATUSES = [self::STATUS_ADDED, self::STATUS_PURCHASED, self::STATUS_PICKED_UP, self::STATUS_AT_YARD];

    /**
     * Whether this user may edit the car: anyone with vehicles.edit, or the
     * person who added it (vehicles.edit_own) while it has not been loaded.
     */
    public function editableBy(?\App\Models\User $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->can('vehicles.edit')) {
            return true;
        }

        return $user->can('vehicles.edit_own')
            && (int) $this->created_by === (int) $user->id
            && in_array($this->status, self::PRE_LOADING_STATUSES, true);
    }

    public function getVehicleLabelAttribute(): string
    {
        if ($this->isCargo()) {
            return $this->cargo_description ?: ($this->title ?? '');
        }

        return $this->buildVehicleLabel() ?: ($this->title ?? '');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusesFor($this->cargo_type)[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    /**
     * Move to a status, stamp the matching date and record it in the history.
     */
    public function transitionTo(string $status, $date = null, ?string $note = null, ?int $containerId = null): void
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $this->status = $status;
        $dateFields = $this->isGeneralCargo() ? self::CLEARING_STATUS_DATE_FIELDS : self::STATUS_DATE_FIELDS;
        if (isset($dateFields[$status])) {
            $field = $dateFields[$status];
            $this->{$field} = $date;
        }
        $this->save();

        // A clearing job moves all of its containers together.
        if ($this->isGeneralCargo() && isset(self::CLEARING_CONTAINER_CASCADE[$status])) {
            [$column, $containerStatus] = self::CLEARING_CONTAINER_CASCADE[$status];
            $this->cargoContainers()->update([$column => $date->toDateString(), 'status' => $containerStatus]);
        }

        $this->milestones()->create([
            'status' => $status,
            'occurred_on' => $date,
            'note' => $note,
            'container_id' => $containerId,
            'user_id' => auth()->id(),
        ]);
    }

    public function quotes()
    {
        return $this->hasMany(\Modules\Quoting\Entities\Quote::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    /** Containers that carry this clearing job's general cargo. */
    public function cargoContainers()
    {
        return $this->hasMany(Container::class, 'cargo_shipment_id')->orderBy('id');
    }

    public function milestones()
    {
        return $this->hasMany(ShipmentMilestone::class)->orderByDesc('occurred_on')->orderByDesc('id');
    }

    public function charges()
    {
        return $this->hasMany(ShipmentCharge::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
