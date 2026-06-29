<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Discount;

class CartDiscount
{
    public function __construct(
        public CartLine|Cart $model,
        public Discount $discount
    ) {
        //
    }
}
