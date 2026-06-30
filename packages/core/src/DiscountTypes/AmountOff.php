<?php

namespace Lunar\Core\DiscountTypes;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Collection;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;

class AmountOff extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return __('lunarpanel::discount.form.amount_off.heading');
    }

    /**
     * Called just before cart totals are calculated.
     */
    public function apply(Cart $cart): Cart
    {
        $data = $this->discount->data;

        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        if ($data['fixed_value'] ?? false) {
            return $this->applyFixedValue(
                values: $data['fixed_values'] ?? [],
                cart: $cart,
            );
        }

        return $this->applyPercentage(
            value: $data['percentage'] ?? 0,
            cart: $cart
        );
    }

    /**
     * Apply fixed value discount
     */
    private function applyFixedValue(array $values, Cart $cart): Cart
    {
        $currency = $cart->currency;
        $value = (int) ($values[$currency->code] ?? 0);

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

    /**
     * Return the eligible lines for the discount.
     */
    protected function getEligibleLines(Cart $cart): \Illuminate\Support\Collection
    {
        $collectionIds = $this->discount->collections->where('pivot.type', 'limitation')->pluck('id');
        $collectionExclusionIds = $this->discount->collections->where('pivot.type', 'exclusion')->pluck('id');

        $brandIds = $this->discount->brands->where('pivot.type', 'limitation')->pluck('id');
        $brandExclusionIds = $this->discount->brands->where('pivot.type', 'exclusion')->pluck('id');

        $productIds = $this->discount->discountableLimitations
            ->reject(fn ($limitation) => ! $limitation->discountable)
            ->map(fn ($limitation) => get_class($limitation->discountable).'::'.$limitation->discountable->id);

        $productExclusionIds = $this->discount->discountableExclusions
            ->reject(fn ($limitation) => ! $limitation->discountable)
            ->map(fn ($limitation) => get_class($limitation->discountable).'::'.$limitation->discountable->id);

        $lines = $cart->lines;

        if ($collectionIds->count()) {
            $lines = $lines->filter(function ($line) use ($collectionIds) {
                return $line->purchasable->product()->whereHas('collections', function ($query) use ($collectionIds) {
                    $query->whereIn((new Collection)->getTable().'.id', $collectionIds);
                })->exists();
            });
        }

        if ($collectionExclusionIds->count()) {
            $lines = $lines->reject(function ($line) use ($collectionExclusionIds) {
                return $line->purchasable->product()->whereHas('collections', function ($query) use ($collectionExclusionIds) {
                    $query->whereIn((new Collection)->getTable().'.id', $collectionExclusionIds);
                })->exists();
            });
        }

        if ($brandIds->count()) {
            $lines = $lines->reject(function ($line) use ($brandIds) {
                return ! $brandIds->contains($line->purchasable->product->brand_id);
            });
        }

        if ($brandExclusionIds->count()) {
            $lines = $lines->reject(function ($line) use ($brandExclusionIds) {
                return $brandExclusionIds->contains($line->purchasable->product->brand_id);
            });
        }

        if ($productIds->count()) {
            $lines = $lines->filter(function ($line) use ($productIds) {
                return $productIds->contains(get_class($line->purchasable).'::'.$line->purchasable->id) || $productIds->contains(get_class($line->purchasable->product).'::'.$line->purchasable->product->id);
            });
        }

        if ($productExclusionIds->count()) {
            $lines = $lines->reject(function ($line) use ($productExclusionIds) {
                return $productExclusionIds->contains(get_class($line->purchasable).'::'.$line->purchasable->id) || $productExclusionIds->contains(get_class($line->purchasable->product).'::'.$line->purchasable->product->id);
            });
        }

        return $lines;
    }

    /**
     * Apply the percentage to the cart line.
     */
    private function applyPercentage(float $value, Cart $cart): Cart
    {
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
