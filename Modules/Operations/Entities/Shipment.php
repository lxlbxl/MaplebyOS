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
 * One vehicle moving through the pipeline (one row of the tracking sheet).
 */
class Shipment extends Model
{
    use SoftDeletes;

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

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            $shipment->date_added ??= Carbon::today();
            $shipment->status ??= self::STATUS_ADDED;
            if (empty($shipment->tracking_id)) {
                $shipment->tracking_id = app(TrackingIdGenerator::class)->next(Carbon::parse($shipment->date_added));
            }
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = $shipment->tracking_id;
            }
        });

        static::saving(function (Shipment $shipment) {
            if ($shipment->vin !== null) {
                $shipment->vin = VinDecoder::clean($shipment->vin) ?: null;
            }
            if (empty($shipment->title) || $shipment->isDirty(['vehicle_year', 'vehicle_make', 'vehicle_model'])) {
                $shipment->title = $shipment->buildVehicleLabel() ?: ($shipment->title ?: $shipment->tracking_id);
            }
        });
    }

    public function buildVehicleLabel(): string
    {
        return trim(implode(' ', array_filter([$this->vehicle_year, $this->vehicle_make, $this->vehicle_model])));
    }

    public function getVehicleLabelAttribute(): string
    {
        return $this->buildVehicleLabel() ?: ($this->title ?? '');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    /**
     * Move to a status, stamp the matching date and record it in the history.
     */
    public function transitionTo(string $status, $date = null, ?string $note = null, ?int $containerId = null): void
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $this->status = $status;
        if (isset(self::STATUS_DATE_FIELDS[$status])) {
            $field = self::STATUS_DATE_FIELDS[$status];
            $this->{$field} = $date;
        }
        $this->save();

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
