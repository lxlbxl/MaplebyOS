<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ShipmentMilestone extends Model
{
    protected $fillable = ['shipment_id', 'container_id', 'status', 'occurred_on', 'note', 'user_id'];

    protected $casts = [
        'occurred_on' => 'date:Y-m-d',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
