<?php

namespace Lunar\Tests\Stubs;

use Lunar\DiscountTypes\AbstractDiscountType;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;

class TestDiscountType extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Test Discount Type';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function apply(Cart $cart): Cart
    {
        return $cart;
    }
}
