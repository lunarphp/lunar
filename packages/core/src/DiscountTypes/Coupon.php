<?php

namespace GetCandy\DiscountTypes;

use GetCandy\Base\DataTransferObjects\CartDiscount;
use GetCandy\DataTypes\Price;
use GetCandy\Facades\Discounts;
use GetCandy\Models\CartLine;
use GetCandy\Models\Collection;
use GetCandy\Models\Discount;

class Coupon
{
    protected Discount $discount;

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
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Coupon';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function execute(CartLine $cartLine): CartLine
    {
        $data = $this->discount->data;

        $cartCoupon = strtoupper($cartLine->cart->meta->coupon ?? null);
        $conditionCoupon = strtoupper($data['coupon'] ?? null);

        $passes = $cartCoupon && ($cartCoupon === $conditionCoupon);

        if (! $passes) {
            return $cartLine;
        }

        $collectionIds = $this->discount->collections->pluck('id');

        if ($collectionIds->count()) {
            $passes = $cartLine->purchasable->product()->whereHas('collections', function ($query) use ($collectionIds) {
                $query->whereIn((new Collection)->getTable().'.id', $collectionIds);
            })->exists();
        }

        if (! $passes) {
            return $cartLine;
        }

        $cartLine->discount = $this->discount;

        Discounts::addApplied(
            new CartDiscount($cartLine, $this->discount)
        );

        return $this->applyPercentage(
            value: $data['value'],
            cartLine: $cartLine
        );
    }

    /**
     * Apply the percentage to the cart line.
     *
     * @param  int  $value
     * @param  CartLine  $cartLine
     * @return CartLine
     */
    private function applyPercentage($value, $cartLine): CartLine
    {
        $subTotal = $cartLine->subTotal->value;
        $amount = (int) round($subTotal * ($value / 100));

        $cartLine->discountTotal = new Price(
            $amount,
            $cartLine->cart->currency,
            1
        );

        return $cartLine;
    }
}
