<?php

namespace Lunar\Base\ValueObjects\Cart;

use Lunar\Models\CartLine;

class DiscountBreakdownLine
{
    public function __construct(
        public CartLine $lines,
        public int $quantity,
    ) {
        //
    }
}
