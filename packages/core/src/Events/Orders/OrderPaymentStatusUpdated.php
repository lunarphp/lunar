<?php

namespace Lunar\Core\Events\Orders;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Payment\PaymentState;

class OrderPaymentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public ?PaymentState $previousStatus,
        public PaymentState $newStatus,
    ) {}
}
