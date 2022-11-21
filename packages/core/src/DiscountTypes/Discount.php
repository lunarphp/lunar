<?php

namespace Lunar\DiscountTypes;

use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Collection;

class Discount extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Discount';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function apply(Cart $cart): Cart
    {
        $data = $this->discount->data;

        $cartCoupon = strtoupper($cart->coupon_code ?? null);
        $conditionCoupon = strtoupper($data['coupon'] ?? null);

        $passes = $cartCoupon && ($cartCoupon === $conditionCoupon);

        $minSpend = $data['min_prices'][$cart->currency->code] ?? null;

        $lines = $this->getEligibleLines($cart);

        if (! $passes || ($minSpend && $minSpend < $lines->sum('subTotal.value'))) {
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

        $value = ($values[$currency->code] ?? 0) * 100;

        $lines = $this->getEligibleLines($cart);

        if (! $value || $lines->sum('subTotal.value') < $value) {
            return $cart;
        }

        $cart->cartDiscountAmount = new Price(
            $value,
            $currency,
            1
        );

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
     * @return Collection
     */
    private function getEligibleLines(Cart $cart)
    {
        $collectionIds = $this->discount->collections->pluck('id');
        $brandIds = $this->discount->brands->pluck('id');

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
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        return $cart;
    }
}
