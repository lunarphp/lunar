<?php

namespace Lunar\Core\DiscountTypes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\DiscountType;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Discount;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;

abstract class AbstractDiscountType implements DiscountType
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
        /** @var Discount $discount */
        $this->discount = $discount;

        $this->discount->loadMissing([
            'customers',
            'collections',
            'brands',
            'discountableLimitations.discountable',
            'discountableExclusions.discountable',
            'discountableConditions.discountable',
            'discountableRewards.discountable',
        ]);

        return $this;
    }

    /**
     * Mark a discount as used
     */
    public function markAsUsed(Cart $cart): self
    {
        /** @var Cart $cart */
        $this->discount->uses = $this->discount->uses + 1;

        if ($user = $cart->user) {
            $this->discount->users()->attach($user);
        }

        return $this;
    }

    /**
     * Return the eligible lines for the discount.
     */
    protected function getEligibleLines(Cart $cart): Collection
    {
        /** @var Cart $cart */
        return $cart->lines;
    }

    /**
     * Check if discount's conditions met.
     */
    protected function checkDiscountConditions(Cart $cart): bool
    {
        /** @var Cart $cart */
        $cart->loadMissing('currency');
        $data = $this->discount->data;

        $customerIds = $this->discount->customers->pluck('id');

        if ((! $customerIds->isEmpty() && ! $cart->customer) || (! $customerIds->isEmpty() && ! $customerIds->contains($cart->customer_id))) {
            return false;
        }

        $cartCoupon = strtoupper($cart->coupon_code ?? '');
        $conditionCoupon = strtoupper($this->discount->coupon ?? '');

        $validCoupon = filled($conditionCoupon) ? ($cartCoupon === $conditionCoupon) : true;

        $minSpend = (int) ($data['min_prices'][$cart->currency->code] ?? 0) / (int) $cart->currency->factor;
        $minSpend = (int) bcmul($minSpend, $cart->currency->factor);

        $lines = $this->getEligibleLines($cart);
        $validMinSpend = $minSpend ? $minSpend < $lines->sum('subTotal.value') : true;

        // A cart that already consumed this discount when its draft order was
        // created must not then be blocked by its own use. Otherwise creating
        // that order again — a declined card and a retry — re-prices it without
        // the discount the shopper was quoted.
        $alreadyConsumed = $cart->consumedDiscountIds()->contains($this->discount->id);

        $validMaxUses = $this->discount->max_uses
            ? ($alreadyConsumed || $this->discount->uses < $this->discount->max_uses)
            : true;

        if (! $alreadyConsumed && $validMaxUses && $this->discount->max_uses_per_user) {
            $validMaxUses = $cart->user && ($this->usesByUser($cart->user) < $this->discount->max_uses_per_user);
        }

        return $validCoupon && $validMinSpend && $validMaxUses;
    }

    /**
     * Check if discount's conditions met.
     *
     * @return self
     */
    protected function addDiscountBreakdown(Cart $cart, DiscountBreakdown $breakdown)
    {
        /** @var Cart $cart */
        if (! $cart->discountBreakdown) {
            $cart->discountBreakdown = collect();
        }
        $cart->discountBreakdown->push($breakdown);

        return $this;
    }

    /**
     * Check how many times this discount has been used by the logged in user's customers
     *
     * @return int
     */
    protected function usesByUser(Authenticatable $user)
    {
        return $this->discount->users()
            ->whereUserId($user->getKey())
            ->count();
    }
}
