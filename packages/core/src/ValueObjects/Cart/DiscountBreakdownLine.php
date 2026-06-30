<?php

namespace Lunar\Core\ValueObjects\Cart;

use Lunar\Core\Models\CartLine;

class DiscountBreakdownLine
{
    public function __construct(
        public CartLine $line,
        public int $quantity,
    ) {
        //
    }
}
