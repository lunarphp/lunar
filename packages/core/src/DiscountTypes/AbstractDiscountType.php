<?php

namespace Lunar\Core\DiscountTypes;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Discounts\CountsCustomerRedemptions;
use Lunar\Core\Contracts\DiscountType;
use Lunar\Core\Enums\DiscountRedemptionMethod;
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

        $validCoupon = true;

        if ($this->discount->redemption === DiscountRedemptionMethod::Coupon) {
            $coupon = $cart->loadMissing('coupon')->coupon;

            $validCoupon = $coupon?->discount_id === $this->discount->id && $coupon->isUsable();
        }

        $minSpend = (int) ($data['min_prices'][$cart->currency->code] ?? 0) / (int) $cart->currency->factor;
        $minSpend = (int) bcmul($minSpend, $cart->currency->factor);

        $lines = $this->getEligibleLines($cart);
        $validMinSpend = $minSpend ? $minSpend < $lines->sum('subTotal.value') : true;

        $validMaxUses = $this->discount->max_uses
            ? $this->discount->uses < $this->discount->max_uses
            : true;

        if ($validMaxUses && $this->discount->max_uses_per_user) {
            $validMaxUses = app(CountsCustomerRedemptions::class)->execute($this->discount, $cart) < $this->discount->max_uses_per_user;
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
}
