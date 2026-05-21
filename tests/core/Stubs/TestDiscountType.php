<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Contracts\Cart as CartContract;

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
