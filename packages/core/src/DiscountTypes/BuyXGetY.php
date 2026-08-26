<?php

namespace Lunar\DiscountTypes;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Base\Purchasable;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdownLine;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class BuyXGetY extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return __('lunarpanel::discount.form.buy_x_get_y.heading');
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
    public function apply(CartContract $cart): CartContract
    {
        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        $data = $this->discount->data;

        $minQty = $data['min_qty'] ?? null;
        $rewardQty = $data['reward_qty'] ?? 1;
        $maxRewardQty = $data['max_reward_qty'] ?? null;
        $automaticallyAddRewards = $data['automatically_add_rewards'] ?? false;

        $hasCollectionDiscountables = $this->discount->discountableConditions
            ->where('discountable_type', LunarCollection::morphName())
            ->isNotEmpty()
            || $this->discount->discountableRewards
                ->where('discountable_type', LunarCollection::morphName())
                ->isNotEmpty();

        $productCollectionIds = collect();

        if ($hasCollectionDiscountables) {
            $products = $cart->lines->map(fn ($line) => $line->purchasable->product)->unique('id');
            $products->loadMissing('collections');
            $productCollectionIds = $products->mapWithKeys(fn ($p) => [$p->id => $p->collections->pluck('id')]);
        }

        // Get all discountables that are eligible.
        $conditions = $cart->lines->reject(function ($line) use ($productCollectionIds) {
            return ! $this->discount->discountableConditions->first(function ($item) use ($line, $productCollectionIds) {
                if ($item->discountable_type == Product::morphName() &&
                    $item->discountable_id == $line->purchasable->product->id
                ) {
                    return true;
                }

                if ($item->discountable_type == ProductVariant::morphName() &&
                    $item->discountable_id == $line->purchasable->id
                ) {
                    return true;
                }

                if ($item->discountable_type == LunarCollection::morphName() &&
                    ($productCollectionIds->get($line->purchasable->product->id) ?? collect())->contains($item->discountable_id)
                ) {
                    return true;
                }

                return false;
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
        $rewardLines = $cart->lines->filter(function ($line) use ($productCollectionIds) {
            return $this->discount->discountableRewards->first(function ($item) use ($line, $productCollectionIds) {
                if ($item->discountable_type == Product::morphName() &&
                    $item->discountable_id == $line->purchasable->product->id
                ) {
                    return true;
                }

                if ($item->discountable_type == ProductVariant::morphName() &&
                    $item->discountable_id == $line->purchasable->id
                ) {
                    return true;
                }

                if ($item->discountable_type == LunarCollection::morphName() &&
                    ($productCollectionIds->get($line->purchasable->product->id) ?? collect())->contains($item->discountable_id)
                ) {
                    return true;
                }

                return false;
            });
        })->sortBy('unitPrice.value');

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
            price: new Price($discountTotal, $cart->currency, 1),
            lines: $affectedLines,
            discount: $this->discount,
        ));

        $cart->discounts->push($this);

        return $cart;
    }

    private function processAutomaticRewards(CartContract $cart, int $remainingRewardQty, Collection $affectedLines, int $discountTotal)
    {
        // Reward lines this run has added, keyed by purchasable. The check below
        // reads $cart->lines, which never receives a line made here, so without
        // this a reward quantity of three opens three lines of one rather than
        // one line of three.
        $addedRewardLines = [];

        // we have lines to add
        if ($remainingRewardQty > 0) {
            // Fulfillable products per collection reward, hydrated once here rather
            // than re-queried on every iteration of the allocation loop below.
            $fulfillableCollectionProducts = [];

            $fulfillableRewards = $this->discount->discountableRewards->filter(function ($discountableReward) use (&$fulfillableCollectionProducts) {
                $rewardItem = $discountableReward->discountable;

                if (! $rewardItem) {
                    return false;
                }

                if ($rewardItem instanceof LunarCollection) {
                    $fulfillableCollectionProducts[$rewardItem->id] = $rewardItem->products()
                        ->with('variants')
                        ->get()
                        ->filter(fn ($p) => $p->variants->first()?->canBeFulfilledAtQuantity(1))
                        ->values();

                    return $fulfillableCollectionProducts[$rewardItem->id]->isNotEmpty();
                }

                if ($rewardItem instanceof Purchasable) {
                    return $rewardItem->canBeFulfilledAtQuantity(1);
                }

                return (bool) $rewardItem->variants->first()?->canBeFulfilledAtQuantity(1);
            });

            if ($fulfillableRewards->isEmpty()) {
                return [$affectedLines, $discountTotal];
            }

            while ($remainingRewardQty > 0) {
                $selectedRewardItem = $fulfillableRewards->random()->discountable;

                if ($selectedRewardItem instanceof LunarCollection) {
                    $product = $fulfillableCollectionProducts[$selectedRewardItem->id]->random();
                    $purchasable = $product->variants->first();
                    $selectedRewardItem = $product;
                } elseif ($selectedRewardItem instanceof Purchasable) {
                    $purchasable = $selectedRewardItem;
                } else {
                    $purchasable = $selectedRewardItem->variants->first();
                }

                if (! $purchasable) {
                    $remainingRewardQty--;

                    continue;
                }

                $rewardKey = $purchasable->getMorphClass().':'.$purchasable->id;

                // How many units of this reward this run has already allocated,
                // since canBeFulfilledAtQuantity below must check against that
                // running total rather than a fixed quantity of 1 each time.
                $allocated = $addedRewardLines[$rewardKey]->quantity ?? 0;

                if (! $purchasable->canBeFulfilledAtQuantity($allocated + 1)) {
                    $remainingRewardQty--;

                    continue;
                }

                // is it already in cart?
                $rewardLine = $addedRewardLines[$rewardKey] ?? $cart->lines->first(function ($line) use ($purchasable) {
                    return $line->purchasable->id == $purchasable->id;
                });

                if ($rewardLine && isset($addedRewardLines[$rewardKey])) {
                    // Another unit of a reward this run already added: raise the
                    // quantity on that line. A line the shopper put in the cart
                    // themselves is left at the quantity they chose, as before.
                    $rewardLine->quantity++;

                    $lineTotal = $rewardLine->unitPrice->value * $rewardLine->quantity;
                    $unitQuantity = $purchasable->getUnitQuantity();

                    $rewardLine->subTotal = new Price($lineTotal, $cart->currency, $unitQuantity);
                    $rewardLine->total = new Price($lineTotal, $cart->currency, $unitQuantity);
                }

                if (! $rewardLine) {
                    $rewardLine = $cart->lines()->make([
                        'purchasable_type' => $purchasable->getMorphClass(),
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

                    $addedRewardLines[$rewardKey] = $rewardLine;
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

                if ($discountTotal > $rewardLine->subTotal->value) {
                    $discountTotal = $rewardLine->subTotal->value;
                }

                $rewardLine->discountTotal = new Price(
                    $discountTotal,
                    $cart->currency,
                    1
                );

                $rewardLine->subTotalDiscounted = new Price(
                    max(0, $rewardLine->subTotal->value - $rewardLine->discountTotal->value),
                    $cart->currency,
                    1
                );

                $rewardLine->meta = $meta;
                $rewardLine->save();

                $remainingRewardQty--;
            }
        }

        return [$affectedLines, $discountTotal];
    }
}
