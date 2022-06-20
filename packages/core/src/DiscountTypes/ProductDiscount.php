<?php

namespace GetCandy\DiscountTypes;

use GetCandy\DataTypes\Price;
use GetCandy\Models\CartLine;
use GetCandy\Models\Discount;

class ProductDiscount
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
        return 'Product Discount';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function execute(CartLine $cartLine): CartLine
    {
        // Is this cart line item the one that's up for the reward?
        $rewardIds = $this->discount->purchasableRewards()->pluck('purchasable_id');

        // First off, is this purchasable relevant?
        if (! $rewardIds->count() || ! $rewardIds->contains($cartLine->purchasable_id)) {
            return $cartLine;
        }

        $data = $this->discount->data;

        // Get our conditions...
        $conditions = $this->discount->purchasableConditions()
            ->pluck('purchasable_id');

        // Do we have a cart item that matches this reward?
        $match = $cartLine->cart->lines->first(function ($line) use ($conditions) {
            return $conditions->contains($line->purchasable_id);
        });

        if (! $match || ($match && $match->quantity < $data['min_qty'])) {
            return $cartLine;
        }

        // Work out the quantity value we want to take off...
        $discountQuantity = $cartLine->quantity - $data['reward_qty'];

        $cartLine->discount = $this->discount;

        $cartLine->discountTotal = new Price(
            $cartLine->unitPrice->value * $discountQuantity,
            $cartLine->cart->currency,
            1
        );

        return $cartLine;
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
