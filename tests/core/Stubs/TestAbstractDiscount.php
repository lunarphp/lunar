<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\DataTypes\Price;
use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Models\Contracts\Cart;

class TestAbstractDiscount extends AbstractDiscountType
{
    public function getName(): string
    {
        return 'Test Discount';
    }

    public function apply(Cart $cart): Cart
    {
        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        foreach ($cart->lines as $line) {
            $line->discountTotal = new Price(100, $cart->currency);
            $line->subTotalDiscounted = new Price(
                900,
                $cart->currency,
                1
            );
        }

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            price: new Price(100, $cart->currency, 1),
            lines: $cart->lines,
            discount: $this->discount,
        ));

        $cart->discounts->push($this);

        return $cart;
    }
}
