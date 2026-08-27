<?php

namespace Lunar\Core\DiscountTypes;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DiscountTypes\Concerns\TargetsCartLines;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;

class PercentageOff extends AbstractDiscountType
{
    use TargetsCartLines;

    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return __('lunar::discounts.types.percentage_off');
    }

    /**
     * Called just before cart totals are calculated.
     */
    public function apply(Cart $cart): Cart
    {
        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        $value = $this->discount->data['percentage'] ?? 0;

        $lines = $this->getEligibleLines($cart);

        $affectedLines = collect();
        $totalDiscount = 0;

        foreach ($lines as $line) {
            $subTotal = $line->subTotalDiscounted ?: $line->subTotal;
            $lineDiscount = $line->discountTotal?->value ?: 0;

            $amount = PriceCalculator::percentage($subTotal->value, $value / 100, $cart->currency);

            // If this line already has a greater discount value
            // don't add this one as they already have a better deal.
            if ($lineDiscount > $amount) {
                continue;
            }

            $totalDiscount += $amount;

            $discountValue = new PriceValue($amount, $cart->currency);

            $line->discountTotal = new PriceValue($lineDiscount + $amount, $cart->currency);

            $line->subTotalDiscounted = $subTotal->subtract($discountValue);

            $affectedLines->push(new DiscountBreakdownLine(
                line: $line,
                quantity: $line->quantity
            ));
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        if ($totalDiscount <= 0) {
            return $cart;
        }

        $cart->discounts->push($this);

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            price: new PriceValue($totalDiscount, $cart->currency),
            lines: $affectedLines,
            discount: $this->discount,
        ));

        return $cart;
    }
}
