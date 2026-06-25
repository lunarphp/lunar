<?php

namespace Lunar\Core\Events\Orders;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Order;

/**
 * Fired the moment an order's `placed_at` is stamped (a draft becomes a real
 * order). Distinct from creation — a draft can exist before it is placed.
 */
class OrderPlaced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}
}
