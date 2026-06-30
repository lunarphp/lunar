<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;

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
            $line->discountTotal = new PriceValue(100, $cart->currency);
            $line->subTotalDiscounted = new PriceValue(900, $cart->currency);
        }

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            price: new PriceValue(100, $cart->currency),
            lines: $cart->lines,
            discount: $this->discount,
        ));

        $cart->discounts->push($this);

        return $cart;
    }
}
