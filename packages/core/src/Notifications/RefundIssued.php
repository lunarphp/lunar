<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

/**
 * Sent when a refund is processed with "notify customer" on. The amount is read
 * from the ledger — the latest successful refund transaction — rather than
 * carried on the event, so a manual resend (which only has the order) shows
 * the same figure. Registered as the `refund-issued` default.
 */
class RefundIssued extends Notification implements AcceptsCustomerMessage
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
            ->subject(__('lunar::notifications.refund_issued.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.refund-issued', [
                'order' => $this->order,
                'reference' => $reference,
                'amount' => $this->latestRefund()?->format('amount'),
                'message' => $this->message,
            ]);
    }

    protected function latestRefund(): ?Transaction
    {
        return $this->order->refunds()->where('success', true)->latest('id')->first();
    }
}
