<?php

namespace Lunar\Core\Base\DataTransferObjects;

use Lunar\Core\Models\Contracts\Cart;
use Lunar\Core\Models\Contracts\CartLine;
use Lunar\Core\Models\Contracts\Discount;

class CartDiscount
{
    public function __construct(
        public CartLine|Cart $model,
        public Discount $discount
    ) {
        //
    }
}
