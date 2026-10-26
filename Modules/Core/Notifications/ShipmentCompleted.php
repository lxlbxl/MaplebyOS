<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Operations\Entities\Shipment;

class ShipmentCompleted extends Notification
{
    use Queueable;

    public $shipment;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function via($notifiable)
    {
        return ['database']; // Add 'mail' or others if needed
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'shipment_completed',
            'message' => 'Shipment ' . $this->shipment->shipment_number . ' has been completed.',
            'url' => route('operations.shipments.index'),
            'shipment_id' => $this->shipment->id,
        ];
    }
}
