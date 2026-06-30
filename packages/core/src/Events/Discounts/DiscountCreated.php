<?php

namespace Lunar\Core\Events\Discounts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Discount;

class DiscountCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Discount $discount,
    ) {}
}
