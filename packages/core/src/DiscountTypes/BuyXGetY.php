<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\ValueObjects\Cart\DiscountBreakdownValue;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Discount;
use Lunar\Models\Product;

class BuyXGetY
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

            $remainder = $rewardLine->quantity % $remainingRewardQty;

            $qtyToAllocate = (int) floor(($remainingRewardQty - $remainder) / $rewardLine->quantity);

            if (! $qtyToAllocate) {
                continue;
            }
            
            $affectedLines->push((object) [
                'line' => $cartLine,
                'quantity' => $qtyToAllocate,
            ]);
            
            $conditionQtyToAllocate = $qtyToAllocate * $rewardQty;
            $conditions->each(function ($conditionLine) use ($affectedLines, &$conditionQtyToAllocate) {
                if (! $conditionQtyToAllocate) {
                    return;
                }
                
                $qtyCanBeApplied = min($conditionQtyToAllocate, $conditionLine->quantity - $affectedLines->firstWhere('line', $conditionLine)?->quantity ?? 0);
                if ($qtyCanBeApplied > 0) {
                    $conditionQtyToAllocate -= $qtyCanBeApplied;
                    
                    $affectedLines->push((object) [
                        'line' => $cartLine,
                        'quantity' => $qtyToAllocate,
                    ]);
                }
            });

            $remainingRewardQty -= $qtyToAllocate;

            $subTotal = $rewardLine->subTotal->value;

            $lineDiscountTotal = $subTotal * $qtyToAllocate;
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

        $cart->discountBreakdown->addDiscount() = new DiscountBreakdownValue(
            discount: $this->discount,
            lines: $affectedLines,
            price: new Price($discountTotal, $cart->currency, 1)
        );

        return $cart;
    }
}
