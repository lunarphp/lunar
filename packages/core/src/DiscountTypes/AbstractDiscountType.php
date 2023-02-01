<?php

namespace Lunar\DiscountTypes;

use Illuminate\Support\Collection;
use Lunar\Base\DiscountTypeInterface;
use Lunar\Models\Cart;
use Lunar\Models\Discount;

abstract class AbstractDiscountType implements DiscountTypeInterface
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * Set the data for the discount to user.
     *
     * @param  array  $data
     * @return self
     */
    public function with(Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Mark a discount as used
     *
     * @return self
     */
    public function markAsUsed(): self
    {
        $this->discount->uses = $this->discount->uses + 1;

        return $this;
    }

    /**
     * Return the eligible lines for the discount.
     *
     * @param  Cart  $cart
     * @return Illuminate\Support\Collection
     */
    protected function getEligibleLines(Cart $cart): Collection
    {
        return $cart->lines;
    }

    /**
     * Check if discount's conditions met.
     *
     * @param  Cart  $cart
     * @return bool
     */
    protected function checkDiscountConditions(Cart $cart): bool
    {
        $data = $this->discount->data;

        $cartCoupon = strtoupper($cart->coupon_code ?? null);
        $conditionCoupon = strtoupper($this->discount->coupon ?? null);

        $validCoupon = $conditionCoupon ? ($cartCoupon === $conditionCoupon) : true;

        $minSpend = $data['min_prices'][$cart->currency->code] ?? null;
        $minSpend = (int) bcmul($minSpend, $cart->currency->factor);

        $lines = $this->getEligibleLines($cart);
        $validMinSpend = $minSpend ? $minSpend < $lines->sum('subTotal.value') : true;

        $validMaxUses = $this->discount->max_uses ? $this->discount->uses < $this->discount->max_uses : true;

        return $validCoupon && $validMinSpend && $validMaxUses;
    }
}
