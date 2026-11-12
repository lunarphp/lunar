<?php

namespace Lunar\Core\Events\Discounts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Order;

/**
 * A placed order took a discount past its `max_uses`. The order stands, since
 * it has already been paid for; listen here to hold it or alert someone.
 */
class DiscountLimitExceeded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public Discount $discount,
        public ?Model $coupon = null,
    ) {}
}
