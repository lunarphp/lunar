<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Models\Order;

/**
 * Sent when an order is cancelled with "notify customer" on, carrying the
 * cancel reason's label. Named OrderCancellation rather than OrderCancelled so
 * it does not clash with the event of that name in listener imports.
 * Registered as the `order-cancelled` default.
 */
class OrderCancellation extends Notification implements AcceptsCustomerMessage
{
    use Queueable;

    public function __construct(
        public Order $order,
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
        $this->order->loadMissing(['billingAddress', 'shippingAddress']);

        $reference = $this->order->reference ?? (string) $this->order->id;

        return (new MailMessage)
            ->subject(__('lunar::notifications.order_cancelled.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.cancellation', [
                'order' => $this->order,
                'reference' => $reference,
                'reason' => $this->order->cancelReasonLabel(),
                'message' => $this->message,
            ]);
    }
}
