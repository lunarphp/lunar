<?php

namespace Lunar\DiscountTypes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Base\DiscountTypeInterface;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Models\Cart;
use Lunar\Models\Discount;

abstract class AbstractDiscountType implements DiscountTypeInterface
{
    /**
     * The instance of the discount.
     */
    public Discount $discount;

    /**
     * Set the data for the discount to user.
     *
     * @param  array  $data
     */
    public function with(Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Mark a discount as used
     */
    public function markAsUsed(Cart $cart): self
    {
        $this->discount->uses = $this->discount->uses + 1;

        if ($user = $cart->user) {
            $this->discount->users()->attach($user);
        }

        return $this;
    }

    /**
     * Return the eligible lines for the discount.
     *
     * @return Illuminate\Support\Collection
     */
    protected function getEligibleLines(Cart $cart): Collection
    {
        return $cart->lines;
    }

    /**
     * Check if discount's conditions met.
     */
    protected function checkDiscountConditions(Cart $cart): bool
    {
        $data = $this->discount->data;

        $cartCoupon = strtoupper($cart->coupon_code ?? null);
        $conditionCoupon = strtoupper($this->discount->coupon ?? null);

        $validCoupon = $cartCoupon ? ($cartCoupon === $conditionCoupon) : blank($conditionCoupon);

        $minSpend = $data['min_prices'][$cart->currency->code] ?? null;
        $minSpend = (int) bcmul($minSpend, $cart->currency->factor);

        $lines = $this->getEligibleLines($cart);
        $validMinSpend = $minSpend ? $minSpend < $lines->sum('subTotal.value') : true;

        $validMaxUses = $this->discount->max_uses ? $this->discount->uses < $this->discount->max_uses : true;

        if ($validMaxUses && $this->discount->max_uses_per_user) {
            $validMaxUses = $cart->user && ($this->usesByUser($cart->user) < $this->discount->max_uses_per_user);
        }

        return $validCoupon && $validMinSpend && $validMaxUses;
    }

    /**
     * Check if discount's conditions met.
     *
     * @param  Cart  $cart
     * @param  Lunar\Base\ValueObjects\Cart\DiscountBreakdown  $breakdown
     * @return self
     */
    protected function addDiscountBreakdown(Cart $cart, DiscountBreakdown $breakdown)
    {
        if (! $cart->discountBreakdown) {
            $cart->discountBreakdown = collect();
        }
        $cart->discountBreakdown->push($breakdown);

        return $this;
    }

    /**
     * Check how many times this discount has been used by the logged in user's customers
     *
     * @param  Illuminate\Contracts\Auth\Authenticatable  $user
     * @return int
     */
    protected function usesByUser(Authenticatable $user)
    {
        return $this->discount->users()
            ->whereUserId($user->getKey())
            ->count();
    }
}
