<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\DiscountTypes\AbstractDiscountType;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Contracts\Cart as CartContract;

class TestDiscountType extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
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
    public function apply(CartContract $cart): CartContract
    {
        return $cart;
    }
}
