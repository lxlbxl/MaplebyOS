<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Operations\Services\ContainerNumber;

/**
 * A container. Either a car container (usually a 40ft holding 4 cars, whose
 * status changes cascade to every vehicle inside it) or a box of a client's
 * general cargo on a clearing job (cargo_shipment_id set).
 */
class Container extends Model
{
    use SoftDeletes, \App\Models\Concerns\HidesRestrictedFields;

    protected static function restrictedFields(): array
    {
        return ['money.view' => \App\Support\Access::CONTAINER_MONEY_FIELDS];
    }

    protected $fillable = [
        'container_number', 'size_type', 'seal_number', 'shipping_line_id', 'booking_number', 'bl_number',
        'vessel', 'voyage', 'port_of_loading', 'port_of_discharge', 'status', 'capacity', 'loaded_on', 'etd',
        'sailed_on', 'eta', 'arrived_on', 'offloaded_on', 'released_on', 'freight_cost', 'freight_currency',
        'notes', 'created_by', 'cargo_shipment_id', 'empty_returned_on',
    ];

    protected $casts = [
        'loaded_on' => 'date:Y-m-d',
        'etd' => 'date:Y-m-d',
        'sailed_on' => 'date:Y-m-d',
        'eta' => 'date:Y-m-d',
        'arrived_on' => 'date:Y-m-d',
        'offloaded_on' => 'date:Y-m-d',
        'released_on' => 'date:Y-m-d',
        'empty_returned_on' => 'date:Y-m-d',
        'freight_cost' => 'decimal:2',
        'capacity' => 'integer',
    ];

    protected $appends = ['status_label', 'number_is_valid'];

    const STATUS_PLANNED = 'planned';
    const STATUS_LOADED = 'loaded';
    const STATUS_SAILED = 'sailed';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_OFFLOADED = 'offloaded';
    const STATUS_RELEASED = 'released';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PLANNED => 'Planned / loading',
            self::STATUS_LOADED => 'Loaded',
            self::STATUS_SAILED => 'Sailed',
            self::STATUS_ARRIVED => 'Arrived Lagos',
            self::STATUS_OFFLOADED => 'Offloaded',
            self::STATUS_RELEASED => 'Released',
        ];
    }

    /** Container status => [date column, vehicle status it pushes to cars]. */
    public const CASCADE = [
        self::STATUS_LOADED => ['loaded_on', Shipment::STATUS_LOADED],
        self::STATUS_SAILED => ['sailed_on', Shipment::STATUS_SAILED],
        self::STATUS_ARRIVED => ['arrived_on', Shipment::STATUS_ARRIVED],
        self::STATUS_OFFLOADED => ['offloaded_on', Shipment::STATUS_OFFLOADED],
        self::STATUS_RELEASED => ['released_on', Shipment::STATUS_CLEARED],
    ];

    protected static function booted(): void
    {
        static::saving(function (Container $container) {
            $container->container_number = ContainerNumber::clean($container->container_number);
            if (!$container->shipping_line_id && $line = ShippingLine::guessFromContainerNumber($container->container_number)) {
                $container->shipping_line_id = $line->id;
            }
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getNumberIsValidAttribute(): bool
    {
        return ContainerNumber::isValid($this->container_number);
    }

    public function isOpen(): bool
    {
        return $this->status !== self::STATUS_RELEASED;
    }

    /** Car slots left. Boxes ride alongside the cars and take no slot. */
    public function freeSlots(): int
    {
        return max(0, $this->capacity - $this->vehicles()->count());
    }

    /**
     * Update the container and push the same status/date to every car in it.
     */
    public function transitionTo(string $status, $date = null, ?string $note = null): void
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        DB::transaction(function () use ($status, $date, $note) {
            $this->status = $status;
            if (isset(self::CASCADE[$status])) {
                $this->{self::CASCADE[$status][0]} = $date;
            }
            $this->save();

            ShipmentMilestone::create([
                'container_id' => $this->id,
                'status' => $status,
                'occurred_on' => $date,
                'note' => $note,
                'user_id' => auth()->id(),
            ]);

            if (isset(self::CASCADE[$status])) {
                foreach ($this->shipments as $shipment) {
                    $shipment->transitionTo(self::CASCADE[$status][1], $date, $note ?? "Container {$this->container_number}", $this->id);
                }
            }
        });
    }

    public function freightSharePerVehicle(): ?float
    {
        $count = $this->vehicles()->count();

        return ($this->freight_cost && $count) ? round((float) $this->freight_cost / $count, 2) : null;
    }

    public function transitDays(): ?int
    {
        $start = $this->sailed_on ?? $this->loaded_on;
        $end = $this->offloaded_on ?? $this->arrived_on;

        return ($start && $end) ? $start->diffInDays($end) : null;
    }

    public function shippingLine()
    {
        return $this->belongsTo(ShippingLine::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class)->orderBy('container_slot')->orderBy('id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    /** Cars in the container (shipments() also includes any boxes riding along). */
    public function vehicles()
    {
        return $this->shipments()->where('cargo_type', Shipment::CARGO_VEHICLE);
    }

    public function boxes()
    {
        return $this->shipments()->where('cargo_type', Shipment::CARGO_BOX);
    }

    public function milestones()
    {
        return $this->hasMany(ShipmentMilestone::class)->whereNull('shipment_id')->orderByDesc('occurred_on');
    }

    /** The clearing job whose general cargo this container carries (null for car containers). */
    public function cargoShipment()
    {
        return $this->belongsTo(Shipment::class, 'cargo_shipment_id');
    }

    public function carriesGeneralCargo(): bool
    {
        return $this->cargo_shipment_id !== null;
    }

    public function scopeForVehicles($query)
    {
        return $query->whereNull('cargo_shipment_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
