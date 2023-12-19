<?php

namespace Lunar\DiscountTypes;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdownLine;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
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
        if ($linesQuantity < $minQty) {
            return 0;
        }

        $result = floor(($linesQuantity / ($minQty ?: 1)) * $rewardQty);

        return $maxRewardQty ? min($result, $maxRewardQty) : $result;
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

        if ($automaticallyAddRewards) {
            [$affectedLines, $discountTotal] = $this->processAutomaticRewards($cart, $remainingRewardQty, $affectedLines, $discountTotal);
        }

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            discount: $this->discount,
            lines: $affectedLines,
            price: new Price($discountTotal, $cart->currency, 1)
        ));

        return $cart;
    }

    private function processAutomaticRewards(Cart $cart, int $remainingRewardQty, Collection $affectedLines, int $discountTotal)
    {
        $automaticLines = $cart->lines->filter(function ($line) {
            return in_array($this->discount->id, array_keys($line->meta->added_by_discount ?? []));
        });

        $remainingRewardQty -= $automaticLines->sum(function ($line) {
            return $line->meta->added_by_discount[$this->discount->id] ?? 0;
        });

        // we have lines to add
        if ($remainingRewardQty > 0) {
            while ($remainingRewardQty > 0) {
                $selectedRewardItem = $this->discount->purchasableRewards->random()->purchasable;
                $purchasable = $selectedRewardItem->variants->first();

                // is it already in cart?
                $rewardLine = $cart->lines->first(function ($line) use ($purchasable) {
                    return $line->purchasable->id == $purchasable->id;
                });

                if (! $rewardLine) {
                    $rewardLine = $cart->lines()->make([
                        'purchasable_type' => get_class($purchasable),
                        'purchasable_id' => $purchasable->id,
                        'quantity' => 1,
                    ]);

                    if (! $cart->freeItems) {
                        $cart->freeItems = collect();
                    }

                    if (! $cart->freeItems->contains($selectedRewardItem)) {
                        $cart->freeItems->push($selectedRewardItem);
                    }

                    $rewardLine = app(Pipeline::class)
                    ->send($rewardLine)
                    ->through(
                        config('lunar.cart.pipelines.cart_lines', [])
                    )->thenReturn(function ($cartLine) {
                        $cartLine->cacheProperties();

                        return $cartLine;
                    });

                    $unitQuantity = $purchasable->getUnitQuantity();

                    $rewardLine->subTotal = new Price($rewardLine->unitPrice->value, $cart->currency, $unitQuantity);
                    $rewardLine->taxAmount = new Price(0, $cart->currency, $unitQuantity);
                    $rewardLine->total = new Price($rewardLine->unitPrice->value, $cart->currency, $unitQuantity);
                }

                $meta = $rewardLine->meta ?? json_decode('{}');
                if (! isset($meta->added_by_discount)) {
                    $meta->added_by_discount = [];
                }

                if (! isset($meta->added_by_discount[$this->discount->id])) {
                    $meta->added_by_discount[$this->discount->id] = 1;
                } else {
                    $meta->added_by_discount[$this->discount->id]++;
                }

                $affectedLine = $affectedLines->first(function ($line) use ($rewardLine) {
                    return $line->line == $rewardLine;
                });

                if (! $affectedLine) {
                    $affectedLines->push(new DiscountBreakdownLine(
                        line: $rewardLine,
                        quantity: 1
                    ));
                } else {
                    $affectedLine->quantity++;
                }

                $unitPrice = $rewardLine->unitPrice->value;

                $discountTotal += $unitPrice;

                $rewardLine->discountTotal = new Price(
                    ($rewardLine->discountTotal?->value ?? 0) + $unitPrice,
                    $cart->currency,
                    1
                );

                $rewardLine->subTotalDiscounted = new Price(
                    $rewardLine->subTotal->value - $rewardLine->discountTotal->value,
                    $cart->currency,
                    1
                );

                $rewardLine->meta = $meta;
                $rewardLine->save();

                $remainingRewardQty--;
            }

        // we have lines to remove
        } elseif ($remainingRewardQty < 0) {
            // while handles the situation where quantity of an item may be more than 1
            while ($remainingRewardQty > 0 && ! empty($automaticLines)) {
                // loop over automatic lines and decrement quantity
                foreach ($automaticLines as $index => $line) {
                    if ($remainingRewardQty >= 0) {
                        continue;
                    }

                    $meta = $line->meta;
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

        return [$affectedLines, $discountTotal];
    }
}
