<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Models\Order;

/**
 * Sent when a capture takes a placed order to `paid` after placement — an
 * authorize-then-capture flow, or an offline payment marked paid in the admin.
 * A capture recorded at checkout does not send this (the rollup fires before
 * `placed_at` is stamped); the order confirmation carries the payment state
 * instead. Registered as the `payment-received` default.
 */
class PaymentReceived extends Notification implements AcceptsCustomerMessage
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
            ->subject(__('lunar::notifications.payment_received.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.payment-received', [
                'order' => $this->order,
                'reference' => $reference,
                'message' => $this->message,
            ]);
    }
}
