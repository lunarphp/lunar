<?php

namespace Lunar\Core\Actions\Orders;

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Notifications\AnonymousNotifiable;
use Lunar\Core\Contracts\Actions\Orders\NotifiesCustomer;
use Lunar\Core\Contracts\CustomerNotificationManifest;
use Lunar\Core\Events\Orders\OrderCustomerNotified;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * Compose and send a chosen customer notification on demand — the interactive
 * counterpart to the automatic, event-driven lifecycle notifications. Resolves
 * the variant from the CustomerNotifications catalogue, delivers it to each
 * recipient, records an `email-notification` activity per recipient, and
 * dispatches OrderCustomerNotified.
 *
 * The send + audit log live here (not the admin layer) so an API caller leaves
 * the same trail as the admin screen.
 */
class NotifyCustomer implements NotifiesCustomer
{
    public function __construct(
        protected CustomerNotificationManifest $catalogue,
        protected Dispatcher $dispatcher,
    ) {}

    public function execute(OrderContract $order, string $notification, ?string $message = null, array $recipients = []): Order
    {
        /** @var Order $order */
        $class = $this->catalogue->get($notification);

        if ($class === null) {
            throw new OrderActionException(
                __('lunar::exceptions.notification_not_sendable', ['notification' => $notification])
            );
        }

        $recipients = $this->resolveRecipients($order, $recipients);

        if ($recipients === []) {
            throw new OrderActionException(
                __('lunar::exceptions.notification_no_recipients')
            );
        }

        $label = $this->catalogue->label($notification);

        foreach ($recipients as $recipient) {
            $this->dispatcher->send(
                (new AnonymousNotifiable)->route('mail', $recipient),
                new $class($order, $message),
            );

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('email-notification')
                ->withProperties([
                    'notification' => $label,
                    'email' => $recipient,
                    'message' => $message,
                ])
                ->log('email-notification');
        }

        OrderCustomerNotified::dispatch($order, $notification, $recipients);

        return $order;
    }

    /**
     * The unique, non-empty recipient set: the explicit list when given, else
     * the order's billing + shipping contact emails.
     *
     * @param  array<int, string>  $recipients
     * @return array<int, string>
     */
    protected function resolveRecipients(Order $order, array $recipients): array
    {
        if ($recipients === []) {
            $recipients = [
                $order->billingAddress?->contact_email,
                $order->shippingAddress?->contact_email,
            ];
        }

        return array_values(array_unique(array_filter($recipients)));
    }
}
