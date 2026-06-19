<?php

namespace Lunar\Core\Events\Orders;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Order;

class OrderCustomerNotified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $notification  The CustomerNotifications key that was sent.
     * @param  array<int, string>  $recipients  The email addresses it was sent to.
     */
    public function __construct(
        public Order $order,
        public string $notification,
        public array $recipients = [],
    ) {}
}
