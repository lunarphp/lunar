<?php

namespace Lunar\Base\ValueObjects\Cart;

use Lunar\Models\Contracts\CartLine;

class DiscountBreakdownLine
{
    public function __construct(
        public CartLine $line,
        public int $quantity,
    ) {
        //
    }
}
