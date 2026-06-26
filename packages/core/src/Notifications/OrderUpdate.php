<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Models\Order;

/**
 * A general-purpose "here is an update on your order" email an admin can send
 * on demand via the NotifyCustomer action. Renders the optional free-text
 * message the admin attached. This is the one default the OrderNotifications
 * catalogue ships (manual-only, order-scoped) so the feature works out of the
 * box; the branded, auto-triggered lifecycle notifications (confirmation,
 * shipped, refunded, …) are a separate piece of work. Override by re-registering
 * the `order-update` key with your own class.
 */
class OrderUpdate extends Notification implements AcceptsCustomerMessage
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
        $reference = $this->order->reference ?? (string) $this->order->id;

        $mail = (new MailMessage)
            ->subject(__('lunar::notifications.order_update.subject', ['reference' => $reference]))
            ->greeting(__('lunar::notifications.order_update.greeting'))
            ->line(__('lunar::notifications.order_update.intro', ['reference' => $reference]));

        if (filled($this->message)) {
            $mail->line($this->message);
        }

        return $mail->line(__('lunar::notifications.order_update.outro'));
    }
}
