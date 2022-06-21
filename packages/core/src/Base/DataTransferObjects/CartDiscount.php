<?php

namespace GetCandy\Base\DataTransferObjects;

use GetCandy\Models\CartLine;
use GetCandy\Models\Discount;

class CartDiscount
{
    public function __construct(
        public CartLine $name,
        public Discount $discount
    ) {
        //
    }
}
