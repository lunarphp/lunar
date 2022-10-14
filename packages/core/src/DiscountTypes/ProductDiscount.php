<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\DataTypes\Price;
use Lunar\Facades\Discounts;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Discount;
use Lunar\Models\Product;

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
     * Return the reward quantity for the discount
     *
     * @param int $linesQuantity
     * @param int $minQty
     * @param int $rewardQty
     * @param int $maxRewardQty
     *
     * @return int
     */
    public function getRewardQuantity($linesQuantity, $minQty, $rewardQty, $maxRewardQty = null)
    {
        $result = ($linesQuantity / $minQty) * $rewardQty;

        if ($maxRewardQty && $result > $maxRewardQty) {
            return $maxRewardQty;
        }

        return $result;
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function apply(Cart $cart): Cart
    {
        $data = $this->discount->data;

        $minQty = $data['min_qty'] ?? null;
        $rewardQty = $data['reward_qty'] ?? 1;
        $maxRewardQty = $data['max_reward_qty'] ?? null;

        // Get the first condition line where the qty check passes.
        $conditions = $cart->lines->reject(function ($line) use ($minQty) {
            $match = $this->discount->purchasableConditions->first(function ($item) use ($line) {
                return $item->purchasable_type == Product::class &&
                    $item->purchasable_id == $line->purchasable->product->id;
            });

            return ! $match || ($minQty && $line->quantity < $minQty);
        });

        if (! $conditions->count()) {
            return $cart;
        }

        // How many products are rewarded?
        $totalRewardQty = $this->getRewardQuantity(
            $conditions->sum('quantity'),
            $minQty,
            $rewardQty,
            $maxRewardQty
        );

        $remainingRewardQty = $totalRewardQty;

        // Get the reward lines and sort by cheapest first.
        $rewardLines = $cart->lines->filter(function ($line) {
            return $this->discount->purchasableRewards->first(function ($item) use ($line) {
                return $item->purchasable_type == Product::class &&
                    $item->purchasable_id == $line->purchasable->product->id;
            });
        })->sortBy('subTotal.value');

        foreach ($rewardLines as $rewardLine) {
            if (! $remainingRewardQty) {
                continue;
            }

            $remainder = $rewardLine->quantity % $remainingRewardQty;

            $qtyToAllocate = ($remainingRewardQty - $remainder) / $rewardLine->quantity;

            $remainingRewardQty -= $qtyToAllocate;

            $subTotal = $rewardLine->subTotal->value;

            $rewardLine->discountTotal = new Price(
                $subTotal * $qtyToAllocate,
                $cart->currency,
                1
            );

            Discounts::addApplied(
                new CartDiscount($rewardLine, $this->discount)
            );
        }

        return $cart;
    }
}
