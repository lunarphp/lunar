<?php

namespace Lunar\Base\DataTransferObjects;

use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Discount;

class CartDiscount
{
    public function __construct(
        public CartLine|Cart $model,
        public Discount $discount
    ) {
        //
    }
}
