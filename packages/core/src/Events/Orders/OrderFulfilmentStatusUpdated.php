<?php

namespace Lunar\Core\Events\Orders;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\FulfilmentStatus;

class OrderFulfilmentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public ?FulfilmentStatus $previousStatus,
        public FulfilmentStatus $newStatus,
        public bool $notify = true,
    ) {}
}
