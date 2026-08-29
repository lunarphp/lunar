<?php

namespace Lunar\Core\DiscountTypes;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DiscountTypes\Concerns\TargetsCartLines;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;

class FixedAmountOff extends AbstractDiscountType
{
    use TargetsCartLines;

    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return __('lunar::discounts.types.fixed_amount_off');
    }

    /**
     * Called just before cart totals are calculated.
     */
    public function apply(Cart $cart): Cart
    {
        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        $currency = $cart->currency;
        $value = (int) ($this->discount->data['amounts'][$currency->code] ?? 0);

        $lines = $this->getEligibleLines($cart);

        $linesSubtotal = $lines->sum(function ($line) {
            return ($line->subTotalDiscounted ?? $line->subTotal)->value;
        });

        if (! $value || $linesSubtotal < $value) {
            return $cart;
        }

        $weights = $lines
            ->mapWithKeys(fn ($line, $key) => [$key => ($line->subTotalDiscounted ?? $line->subTotal)->value])
            ->all();

        $allocations = PriceCalculator::distribute($value, $weights, $currency);

        $remaining = $value;
        $affectedLines = collect();

        foreach ($lines as $key => $line) {
            $subTotal = ($line->subTotalDiscounted ?? $line->subTotal)->value;
            $amount = min($allocations[$key], $subTotal);

            // If this line already has a greater discount value
            // don't add this one as they already have a better deal.
            if ($line->discountTotal->value > $amount) {
                continue;
            }

            $remaining -= $amount;

            $discountValue = new PriceValue($amount, $currency);

            $line->discountTotal = $discountValue;
            $line->subTotalDiscounted = $line->subTotal->subtract($discountValue);

            $affectedLines->push(new DiscountBreakdownLine(
                line: $line,
                quantity: $line->quantity
            ));
        }

        // Any leftover from caps or skipped lines is re-spread across lines
        // that still have remaining subtotal.
        if ($remaining > 0) {
            $lines->filter(fn ($line) => $line->subTotalDiscounted->value > 0)
                ->each(function ($line) use ($affectedLines, $currency, &$remaining) {
                    if ($remaining <= 0) {
                        return;
                    }

                    $amountAvailable = min($line->subTotalDiscounted->value, $remaining);
                    $remaining -= $amountAvailable;

                    $newDiscountTotal = new PriceValue($line->discountTotal->value + $amountAvailable, $currency);

                    $line->discountTotal = $newDiscountTotal;
                    $line->subTotalDiscounted = $line->subTotal->subtract($newDiscountTotal);

                    if (! $affectedLines->first(fn ($breakdownLine) => $breakdownLine->line == $line)) {
                        $affectedLines->push(new DiscountBreakdownLine(
                            line: $line,
                            quantity: $line->quantity
                        ));
                    }
                });
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            price: new PriceValue($value - $remaining, $currency),
            lines: $affectedLines,
            discount: $this->discount,
        ));

        return $cart;
    }
}
