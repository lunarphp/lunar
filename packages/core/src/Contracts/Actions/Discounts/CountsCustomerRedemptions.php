<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Discount;

interface CountsCustomerRedemptions
{
    /**
     * How many times the cart's customer has redeemed the discount.
     */
    public function execute(Discount $discount, Cart $cart): int;
}
