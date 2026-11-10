<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Notifications\Concerns\DescribesFulfilmentLines;

/**
 * Sent when a fulfilment is marked `returned` with "notify customer" on,
 * listing the lines received back. A refund that follows sends its own
 * RefundIssued email. Registered as the `return-received` default.
 */
class ReturnReceived extends Notification
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
        $this->fulfilment->loadMissing(['order.billingAddress', 'order.shippingAddress']);

        $reference = $this->orderReference($this->fulfilment);

        return (new MailMessage)
            ->subject(__('lunar::notifications.return_received.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.return-received', [
                'order' => $this->fulfilment->order,
                'fulfilment' => $this->fulfilment,
                'reference' => $reference,
                'lines' => $this->fulfilmentLineRows($this->fulfilment),
                'message' => $this->message,
            ]);
    }
}
