<?php

namespace Lunar\Base\ValueObjects\Cart;

use Illuminate\Support\Collection;
use Lunar\DataTypes\Price;

class ShippingBreakdown
{
    public function __construct(
        public ?Collection $items = null
    ) {
        $this->items = $items ?: collect();
    }
}
