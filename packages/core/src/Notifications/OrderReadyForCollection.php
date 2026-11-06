<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Notifications\Concerns\DescribesFulfilmentLines;

/**
 * Sent when a collection fulfilment reaches `ready-for-collection` with
 * "notify customer" on: the lines to collect and the fulfilment's location.
 * Registered as the `order-ready-for-collection` default.
 */
class OrderReadyForCollection extends Notification
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
            ->subject(__('lunar::notifications.order_ready_for_collection.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.ready-for-collection', [
                'order' => $this->fulfilment->order,
                'fulfilment' => $this->fulfilment,
                'reference' => $reference,
                'location' => $this->fulfilment->location?->name,
                'lines' => $this->fulfilmentLineRows($this->fulfilment),
                'message' => $this->message,
            ]);
    }
}
