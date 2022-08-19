<?php

namespace GetCandy\Tests\Stubs;

use GetCandy\DiscountTypes\AbstractDiscountType;
use GetCandy\Models\CartLine;

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
    public function execute(CartLine $cartLine): CartLine
    {
        return $cartLine;
    }
}
