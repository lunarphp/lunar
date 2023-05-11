<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdownLine;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Discount;
use Lunar\Models\Product;

class BuyXGetY extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return 'Buy X Get Y';
    }

    /**
     * Return the reward quantity for the discount
     *
     * @param  int  $linesQuantity
     * @param  int  $minQty
     * @param  int  $rewardQty
     * @param  int  $maxRewardQty
     * @return int
     */
    public function getRewardQuantity($linesQuantity, $minQty, $rewardQty, $maxRewardQty = null)
    {
        $result = ($linesQuantity / ($minQty ?: 1)) * $rewardQty;

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

        // Get all purchasables that are eligible.
        $conditions = $cart->lines->reject(function ($line) {
            return ! $this->discount->purchasableConditions->first(function ($item) use ($line) {
                return $item->purchasable_type == Product::class &&
                    $item->purchasable_id == $line->purchasable->product->id;
            });
        });

        $totalQuantity = $conditions->sum('quantity');

        if (! $conditions->count() || ($minQty && $totalQuantity < $minQty)) {
            return $cart;
        }

        // How many products are rewarded?
        $totalRewardQty = $this->getRewardQuantity(
            $totalQuantity,
            $minQty,
            $rewardQty,
            $maxRewardQty
        );

        if (! $totalRewardQty) {
            return $cart;
        }

        $remainingRewardQty = $totalRewardQty;

        $affectedLines = collect();
        $discountTotal = 0;

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

            $remainder = (int) floor($remainingRewardQty);
            $qtyToAllocate = $remainder;

            if ($rewardLine->quantity < $remainder) {
                $remainder = $rewardLine->quantity % $remainingRewardQty;
                $qtyToAllocate = (int) round(($remainingRewardQty - $remainder) / $rewardLine->quantity);
            }

            if ($rewardLine->quantity == 1 && $remainder) {
                $qtyToAllocate = 1;
                $remainder = $remainder - 1;
            }

            if (! $qtyToAllocate) {
                continue;
            }

            $affectedLines->push(new DiscountBreakdownLine(
                line: $rewardLine,
                quantity: $qtyToAllocate
            ));

            $conditionQtyToAllocate = $qtyToAllocate * ($minQty - $rewardQty);

            $conditions->each(function ($conditionLine) use ($affectedLines, &$conditionQtyToAllocate) {
                if (! $conditionQtyToAllocate) {
                    return;
                }

                $qtyCanBeApplied = min($conditionQtyToAllocate, $conditionLine->quantity - ($affectedLines->firstWhere('line', $conditionLine)?->quantity ?? 0));
                if ($qtyCanBeApplied > 0) {
                    $conditionQtyToAllocate -= $qtyCanBeApplied;

                    $affectedLines->push(new DiscountBreakdownLine(
                        line: $conditionLine,
                        quantity: $qtyCanBeApplied
                    ));
                }
            });

            $remainingRewardQty -= $qtyToAllocate;

            $subTotal = $rewardLine->subTotal->value;
            $unitPrice = $rewardLine->unitPrice->value;

            $lineDiscountTotal = $unitPrice * $qtyToAllocate;
            $discountTotal += $lineDiscountTotal;

            $rewardLine->discountTotal = new Price(
                $lineDiscountTotal,
                $cart->currency,
                1
            );

            $rewardLine->subTotalDiscounted = new Price(
                $subTotal - $lineDiscountTotal,
                $cart->currency,
                1
            );

            if (! $cart->freeItems) {
                $cart->freeItems = collect();
            }

            $cart->freeItems->push($rewardLine->purchasable);
        }

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            discount: $this->discount,
            lines: $affectedLines,
            price: new Price($discountTotal, $cart->currency, 1)
        ));

        return $cart;
    }
}
