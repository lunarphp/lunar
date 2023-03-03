<?php

namespace Lunar\DiscountTypes;

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
        $automaticallyAddRewards = $data['automatically_add_rewards'] ?? false;
        
        // remove any existing apportionment data made by this discount
        $this->removeApportionmentMeta($cart->lines);

        // Get the first condition line where the qty check passes.
        $conditions = $cart->lines->reject(function ($line) use ($minQty) {
            $match = $this->discount->purchasableConditions->first(function ($item) use ($line) {
                return $item->purchasable_type == Product::class &&
                    $item->purchasable_id == $line->purchasable->product->id;
            });

            // qty needs updated to consider that some qty may be 'used up' already by other discounts
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
        
        // ignore (for now) any lines added by this discount
        $lines = $cart->lines->reject(function ($line) {
            return in_array($this->discount->id, array_keys($line->meta->added_by_discount ?? []));
        });

        // Get the reward lines and sort by cheapest first.
        $rewardLines = $lines->filter(function ($line) {
            return $this->discount->purchasableRewards->first(function ($item) use ($line) {
                return $item->purchasable_type == Product::class &&
                    $item->purchasable_id == $line->purchasable->product->id;
            });
        })->sortBy('subTotal.value');

        foreach ($rewardLines as $rewardLine) {
            if (! $remainingRewardQty) {
                continue;
            }

            // qty needs updated to consider that some qty may be 'used up' already by other discounts
            $remainder = $rewardLine->quantity % $remainingRewardQty;

            $qtyToAllocate = (int) floor(($remainingRewardQty - $remainder) / $rewardLine->quantity);

            if (! $qtyToAllocate) {
                continue;
            }

            $remainingRewardQty -= $qtyToAllocate;

            $subTotal = $rewardLine->subTotal->value;

            $discountTotal = $subTotal * $qtyToAllocate;

            $rewardLine->discountTotal = new Price(
                (isset($rewardLine->discountTotal) ? $rewardLine->discountTotal->value : 0) + $discountTotal,
                $cart->currency,
                1
            );

            $rewardLine->subTotalDiscounted = new Price(
                $subTotal - $discountTotal,
                $cart->currency,
                1
            );
            
            $this->markLinesForApportionment($rewardLine, $qtyToAllocate, $rewardQty, $conditions);

            if (! $cart->freeItems) {
                $cart->freeItems = collect();
            }

            $cart->freeItems->push($rewardLine->purchasable);
        }
        
        if ($automaticallyAddRewards) {
            $cart = $this->processAutomaticRewards($cart, $remainingRewardQty);
        }
        
        $this->applyApportionment($cart);
                
        return $cart;
    }
    
    private function applyApportionment($cart)
    {
        $lines = $cart->lines
            ->filter(function ($line) {
                return isset($line->meta->discount_applied) && isset($line->meta->discount_applied->{$this->discount->id});
            });
                        
        $discountAllocated = 0;
        $discountTotal = $lines->sum('discountTotal.value');
        $packSubtotal = $lines->sum(function ($line) {
            return $line->unitPrice->value * ($line->meta->discount_applied->{$this->discount->id} ?? 0);
        });
                
        $lines->each(function ($line) use ($cart, $packSubtotal, $discountTotal, &$discountAllocated) {
            
            $lineSubtotal = $line->unitPrice->value * ($line->meta->discount_applied->{$this->discount->id} ?? 0);
            $percentageShareOfDiscount = $lineSubtotal / $packSubtotal;
            $lineDiscountAmount = (int) floor($lineSubtotal * $percentageShareOfDiscount);
                                                
            $discountAllocated += $lineDiscountAmount;
            
            $line->apportionedDiscount = new Price(
                (isset($line->apportionedDiscount) ? $line->apportionedDiscount->value : 0) + $lineDiscountAmount,
                $cart->currency,
                1
            );
                
        });
                
        if ($discountTotal > $discountAllocated) {
            $discountRemaining = $discountTotal - $discountAllocated;
            
            $firstLine = $lines->first();
            $firstLine->apportionedDiscount = new Price(
                $firstLine->apportionedDiscount->value + $discountRemaining,
                $cart->currency,
                1
            );
        }
    }
    
    private function markLinesForApportionment($rewardLine, $qtyToAllocate, $rewardQty, $conditions)
    {            
        // mark this line as part of a discount 'pack' so we can apportion tax correctly
        $meta = (object) $rewardLine->meta ?? json_decode('{}');
        if (! isset($meta->discount_applied)) {
            $meta->discount_applied = (object)[];
        }
        $meta->discount_applied->{$this->discount->id} = $qtyToAllocate;
        $rewardLine->meta = $meta;
        
        // loop over condition lines and mark as part of discount 'pack'
        $conditionQtyToAllocate = $qtyToAllocate * $rewardQty;
        $conditions->each(function ($conditionLine) use ($conditionQtyToAllocate) {
            $meta = (object) $conditionLine->meta ?? json_decode('{}');
            if (! isset($meta->discount_applied)) {
                $meta->discount_applied = (object)[];
            }
            if (! isset($meta->discount_applied->{$this->discount->id})) {
                $meta->discount_applied->{$this->discount->id} = 0;
            }

            $qtyAlreadyAllocated = $meta->discount_applied->{$this->discount->id} ?? 0;
            if ($qtyAlreadyAllocated >= $conditionLine->quantity) {
                return;
            }
            
            $qtyToAllocate = min($conditionLine->quantity - $qtyAlreadyAllocated, $conditionQtyToAllocate);
            $meta->discount_applied->{$this->discount->id} += $qtyToAllocate;
            $conditionLine->meta = $meta;
        });
    }
    
    private function removeApportionmentMeta($lines)
    {
        return $lines->each(function ($line) {
            $meta = $line->meta;
            unset($meta->discount_applied->{$this->discount->id});
            $line->meta = $meta;
        }); 
    }
      
    private function processAutomaticRewards(Cart $cart, int $remainingRewardQty) 
    {  
        $automaticLines = $cart->lines->filter(function ($line) {
            return in_array($this->discount->id, array_keys($line->meta->added_by_discount ?? []));
        });
        
        $remainingRewardQty -= $automaticLines->sum(function ($line) {
            return $line->meta->added_by_discount[$this->discount->id] ?? 0;
        });
        
        $automaticLines = $automaticLines->all();
        
        // we have lines to add
        if ($remainingRewardQty > 0) {
            
            while ($remainingRewardQty > 0) {
                
                $rewardLine = $this->discount->purchasableRewards->random()->purchasable;
                $purchasable = $rewardLine->variants->first();
                
                // is it already in cart?
                $line = $cart->lines->first(function ($line) use ($purchasable) {
                    return $line->purchasable->id == $purchasable->id;
                });
                
                if (! $line) {
                    $line = $cart->lines()->make([
                        'purchasable_type' => get_class($purchasable),
                        'purchasable_id' => $purchasable->id,
                        'quantity' => 1,
                    ]);
                    
                    if (! $cart->freeItems) {
                        $cart->freeItems = collect();
                    }
        
                    if (! $cart->freeItems->contains($rewardLine->purchasable)) {
                        $cart->freeItems->push($rewardLine->purchasable);
                    }
                }
                
                // do we need discountTotal and subtotalDiscounted ?
                
                $meta = (object) $line->meta ?? json_decode('{}');
                if (! isset($meta->added_by_discount)) {
                    $meta->added_by_discount = [];
                }
                
                if (! isset($meta->added_by_discount[$this->discount->id])) {
                    $meta->added_by_discount[$this->discount->id] = 1;    
                } else {
                    $meta->added_by_discount[$this->discount->id]++;
                }
                
                $line->meta = $meta;
                $line->save();
                
                $remainingRewardQty--;
            }
            
        // we have lines to remove
        } else if ($remainingRewardQty < 0) {
            
            // while handles the situation where quantity of an item may be more than 1
            while ($remainingRewardQty > 0 && ! empty($automaticLines)) {
                
                // loop over automatic lines and decrement quantity
                foreach ($automaticLines as $index => $line) {
                    if ($remainingRewardQty >= 0) {
                        continue;
                    }
                    
                    $meta = (object) $line->meta ?? json_decode('{}');
                    $addedByDiscountQty = $meta->added_by_discount[$this->discount->id] ?? 0;
                      
                    if ($addedByDiscountQty > 0) {  
                        $line->quantity = $line->quantity - 1;
                        $addedByDiscountQty--;
                        $remainingRewardQty++;
                        
                        if ($addedByDiscountQty < 1) {
                            unset($meta->added_by_discount[$this->discount->id]);   
                        } else {
                            $meta->added_by_discount[$this->discount->id] = $addedByDiscountQty;
                        }
                        
                        if (empty($meta->added_by_discount)) {
                            unset($meta->added_by_discount);
                        }

                        $line->meta = $meta;
                    }
                    
                    if ($line->quantity > 0) {
                        $line->save();
                    } else {
                        $line->delete();
                        $cart->freeItems->remove($line->product);
                    }

                    if ($addedByDiscountQty <= 0) {
                        unset($automaticLines[$index]);
                    }                        
                }
            }

        }         

        return $cart;
    }
}
