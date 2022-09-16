<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\DataTypes\Price;
use Lunar\Facades\Discounts;
use Lunar\Models\CartLine;
use Lunar\Models\Discount;

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
    public function execute(CartLine $cartLine)
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

        // Get all currently applied discounts, filter them out and then
        // if any of the other cart lines are more expensive than this one,
        // remove the discount from the line so only the cheapest item has the
        // discount applied.
        $applied = Discounts::getApplied()->filter(function ($applied) {
            return $applied->discount?->id == $this->discount->id;
        });

        if ($applied->count()) {
            foreach ($applied as $appliedDiscount) {
                if ($appliedDiscount->cartLine->unitPrice->value > $cartLine->unitPrice->value) {
                    $appliedDiscount->cartLine->discount = null;
                    $appliedDiscount->cartLine->discountTotal = null;
                }
            }

            // Is our current cart line the lowest priced item?
            $lowerPrice = $applied->first(function ($discount) use ($cartLine) {
                return $discount->cartLine->unitPrice->value <= $cartLine->unitPrice->value;
            });

            if ($lowerPrice) {
                return $cartLine;
            }
        }

        // Work out the quantity value we want to take off...
        $discountQuantity = $cartLine->quantity - ($data['reward_qty'] + 1);

        $cartLine->discount = $this->discount;

        $cartLine->discountTotal = new Price(
            $cartLine->unitPrice->value * $discountQuantity,
            $cartLine->cart->currency,
            1
        );

        Discounts::addApplied(
            new CartDiscount($cartLine, $this->discount)
        );

        // return $cartLine;
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
