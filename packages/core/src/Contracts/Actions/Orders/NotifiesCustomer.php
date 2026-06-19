<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

interface NotifiesCustomer
{
    /**
     * Compose and send a chosen customer notification for an order: deliver it
     * to each recipient, log an `email-notification` activity per recipient, and
     * dispatch OrderCustomerNotified.
     *
     * @param  string  $notification  A key from the CustomerNotifications catalogue.
     * @param  string|null  $message  Optional free text included in the email.
     * @param  array<int, string>  $recipients  Explicit recipient emails; defaults to
     *                                          the order's billing + shipping contacts.
     */
    public function execute(OrderContract $order, string $notification, ?string $message = null, array $recipients = []): Order;
}
