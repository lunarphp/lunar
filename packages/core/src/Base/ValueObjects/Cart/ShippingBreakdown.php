<?php

namespace Lunar\Base\ValueObjects\Cart;

use Illuminate\Support\Collection;

class ShippingBreakdown
{
    public function __construct(
        public ?Collection $items = null
    ) {
        $this->items = $items ?: collect();
    }

    public function __toString()
    {
        return $this->items->toJson();
    }
}
