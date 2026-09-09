<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Models\Order;

/**
 * The order confirmation, sent when an order is placed: reference, placed
 * date, line summary and totals, addresses and the payment state. Registered
 * as the `order-confirmation` default; re-register the key with your own class
 * to replace it, or publish `lunar.views` to change the template.
 */
class OrderConfirmation extends Notification implements AcceptsCustomerMessage
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
        $this->order->loadMissing(['productLines', 'billingAddress.country', 'shippingAddress.country']);

        $reference = $this->order->reference ?? (string) $this->order->id;

        return (new MailMessage)
            ->subject(__('lunar::notifications.order_confirmation.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.confirmation', [
                'order' => $this->order,
                'reference' => $reference,
                'placedAt' => $this->order->placed_at?->isoFormat('LL'),
                'lines' => $this->order->productLines,
                'message' => $this->message,
            ]);
    }
}
