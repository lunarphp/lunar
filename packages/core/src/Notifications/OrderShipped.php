<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Notifications\Concerns\DescribesFulfilmentLines;

/**
 * Sent when a shipping fulfilment ships with "notify customer" on: the lines in
 * the fulfilment and one row per tracking reference (carrier, tracking number,
 * tracking URL). Constructed with the fulfilment by
 * SendFulfilmentStatusNotifications and delivered through the order's contact
 * routing. Registered as the `order-shipped` default.
 */
class OrderShipped extends Notification
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
        $this->fulfilment->loadMissing(['order.billingAddress', 'order.shippingAddress', 'trackings']);

        $reference = $this->orderReference($this->fulfilment);

        return (new MailMessage)
            ->subject(__('lunar::notifications.order_shipped.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.shipped', [
                'order' => $this->fulfilment->order,
                'fulfilment' => $this->fulfilment,
                'reference' => $reference,
                'lines' => $this->fulfilmentLineRows($this->fulfilment),
                'trackings' => $this->fulfilment->trackings,
                'message' => $this->message,
            ]);
    }
}
