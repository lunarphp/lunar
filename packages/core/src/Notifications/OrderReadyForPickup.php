<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Notifications\Concerns\DescribesFulfilmentLines;

/**
 * Sent when a pickup fulfilment reaches `ready-for-pickup` with
 * "notify customer" on: the lines to pick up and the fulfilment's location.
 * Registered as the `order-ready-for-pickup` default.
 */
class OrderReadyForPickup extends Notification
{
    use DescribesFulfilmentLines;
    use Queueable;

    public function __construct(
        public Fulfilment $fulfilment,
        public ?string $message = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->fulfilment->loadMissing(['order.billingAddress', 'order.shippingAddress', 'location']);

        $reference = $this->orderReference($this->fulfilment);

        return (new MailMessage)
            ->subject(__('lunar::notifications.order_ready_for_pickup.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.ready-for-pickup', [
                'order' => $this->fulfilment->order,
                'fulfilment' => $this->fulfilment,
                'reference' => $reference,
                'location' => $this->fulfilment->location?->name,
                'lines' => $this->fulfilmentLineRows($this->fulfilment),
                'message' => $this->message,
            ]);
    }
}
