<?php

namespace Lunar\DiscountTypes;

use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Collection;

class AmountOff extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return __('lunar::discounts.amount_off.name');
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function apply(Cart $cart): Cart
    {
        $data = $this->discount->data;

        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        if ($data['fixed_value']) {
            return $this->applyFixedValue(
                values: $data['fixed_values'],
                cart: $cart,
            );
        }

        return $this->applyPercentage(
            value: $data['percentage'],
            cart: $cart
        );
    }

    /**
     * Apply fixed value discount
     *
     * @param  array  $values
     * @param  Cart  $cart
     * @return Cart
     */
    private function applyFixedValue(array $values, Cart $cart): Cart
    {
        $currency = $cart->currency;

        $value = (int) bcmul($values[$currency->code] ?? 0, $currency->factor);

        $lines = $this->getEligibleLines($cart);

        if (! $value || $lines->sum('subTotal.value') < $value) {
            return $cart;
        }

        $divisionalAmount = $value / $lines->count();
        $roundedChunk = (int) (round($divisionalAmount, 2));

        $remaining = $value;

        foreach ($lines as $line) {
            if ($line->subTotal->value < $roundedChunk) {
                $amount = $roundedChunk - ($roundedChunk % $line->subTotal->value);
            } else {
                $amount = $roundedChunk;
            }
            $remaining -= $amount;

            $line->discountTotal = new Price(
                $amount,
                $cart->currency,
                1
            );

            $line->subTotalDiscounted = new Price(
                $line->subTotal->value - $amount,
                $cart->currency,
                1
            );
        }

        // Do we have an amount left over? if so, grab the first line that has
        // enough left to apply the remaining too.
        if ($remaining) {
            $line = $cart->lines->first(function ($line) use ($remaining) {
                return (bool) (($line->subTotal->value - $line->discountTotal->value) - $remaining);
            });

            $newDiscountTotal = $line->discountTotal->value + $remaining;

            $line->discountTotal = new Price(
                $newDiscountTotal,
                $cart->currency,
                1
            );

            $line->subTotalDiscounted = new Price(
                $line->subTotal->value - $newDiscountTotal,
                $cart->currency,
                1
            );
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        return $cart;
    }

    /**
     * Return the eligible lines for the discount.
     *
     * @param  Cart  $cart
     * @return \Illuminate\Support\Collection
     */
    protected function getEligibleLines(Cart $cart): \Illuminate\Support\Collection
    {
        $collectionIds = $this->discount->collections->pluck('id');
        $brandIds = $this->discount->brands->pluck('id');
        $productIds = $this->discount->purchasableLimitations->map(fn ($limitation) => get_class($limitation->purchasable).'::'.$limitation->purchasable->id);

        $lines = $cart->lines;

        if ($collectionIds->count()) {
            $lines = $lines->filter(function ($line) use ($collectionIds) {
                return $line->purchasable->product()->whereHas('collections', function ($query) use ($collectionIds) {
                    $query->whereIn((new Collection)->getTable().'.id', $collectionIds);
                })->exists();
            });
        }

        if ($brandIds->count()) {
            $lines = $lines->reject(function ($line) use ($brandIds) {
                return ! $brandIds->contains($line->purchasable->product->brand_id);
            });
        }

        if ($productIds->count()) {
            $lines = $lines->reject(function ($line) use ($productIds) {
                return ! $productIds->contains(get_class($line->purchasable->product).'::'.$line->purchasable->product->id);
            });
        }

        return $lines;
    }

    /**
     * Apply the percentage to the cart line.
     *
     * @param  int  $value
     * @param  CartLine  $cartLine
     * @return CartLine
     */
    private function applyPercentage($value, $cart): Cart
    {
        $lines = $this->getEligibleLines($cart);

        foreach ($lines as $line) {
            $subTotal = $line->subTotal->value;
            $amount = (int) round($subTotal * ($value / 100));

            $line->discountTotal = new Price(
                $amount,
                $cart->currency,
                1
            );

            $line->subTotalDiscounted = new Price(
                $subTotal - $amount,
                $cart->currency,
                1
            );
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        return $cart;
    }
}
