<?php

namespace Lunar\Base\DataTransferObjects;

use Lunar\Models\Contracts\Cart;
use Lunar\Models\Contracts\CartLine;
use Lunar\Models\Contracts\Discount;

class CartDiscount
{
    public function __construct(
        public CartLine|Cart $model,
        public Discount $discount
    ) {
        //
    }
}
