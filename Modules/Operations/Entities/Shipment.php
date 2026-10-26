<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Entities\Client;
use App\Models\User;

class Shipment extends Model
{
    use SoftDeletes;

    protected $table = 'shipments';

    protected $fillable = [
        'title',
        'shipment_number',
        'client_id',
        'status',
        'service_type',
        'origin',
        'destination',
        'location_notes',
        'estimated_completion',
        'total_value',
        'assigned_to',
        'description',
        'vin',
        'vehicle_year',
        'vehicle_make',
        'vehicle_model',
        'vehicle_color',
        'tow_required',
        'driver_name',
        'ownership_documents',
        'date_received',
        'payment_status',
    ];

    protected $casts = [
        'ownership_documents' => 'array',
        'tow_required' => 'boolean',
        'estimated_completion' => 'date',
        'date_received' => 'date',
        'total_value' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RECEIVED = 'received';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DELIVERED = 'delivered';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending/Awaiting',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_ARRIVED => 'Arrived',
            self::STATUS_DELIVERED => 'Delivered',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->shipment_number)) {
                $model->shipment_number = 'SHP-' . strtoupper(str()->random(8));
            }
        });
    }

    public function quotes()
    {
        return $this->hasMany(\Modules\Quoting\Entities\Quote::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
