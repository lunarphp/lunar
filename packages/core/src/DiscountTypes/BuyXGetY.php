<?php

namespace Lunar\Core\DiscountTypes;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Collection as LunarCollection;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdownLine;

class BuyXGetY extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return __('lunar::discounts.types.buy_x_get_y');
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

        // Lines a discount added are not the shopper's purchases: they never
        // count as conditions or take a reward here, so a reward can't keep
        // itself earned. This discount's own lines are allocated below.
        $shopperLines = $cart->lines->reject(fn (CartLine $line) => $line->isAddedByDiscount());

        // Get all discountables that are eligible.
        $conditions = $shopperLines->reject(function ($line) use ($productCollectionIds) {
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
        $rewardLines = $shopperLines->filter(function ($line) use ($productCollectionIds) {
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

            $lineDiscount = $rewardLine->unitPrice->multiply($qtyToAllocate);

            $discountTotal += $lineDiscount->value;

            $rewardLine->discountTotal = $lineDiscount;

            $rewardLine->subTotalDiscounted = $rewardLine->subTotal->subtract($lineDiscount);

            if (! $cart->freeItems) {
                $cart->freeItems = collect();
            }

            $cart->freeItems->push($rewardLine->purchasable);
        }

        if ($automaticallyAddRewards) {
            [$affectedLines, $discountTotal] = $this->processAutomaticRewards($cart, (int) $remainingRewardQty, $affectedLines, $discountTotal);
        }

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            price: new PriceValue($discountTotal, $cart->currency),
            lines: $affectedLines,
            discount: $this->discount,
        ));

        $cart->discounts->push($this);

        return $cart;
    }

    /**
     * Allocate the reward units the shopper's own lines could not take onto
     * lines this discount manages (spec remove-unearned-reward-lines):
     *
     * 1. Keep — its existing added lines, oldest first, keep up to their
     *    current quantity.
     * 2. Raise — units still left raise those lines as far as stock allows.
     * 3. Add — units still left pick a reward candidate and go on a new,
     *    unsaved line.
     *
     * Nothing is written here. Each allocation is recorded in the breakdown,
     * and ReconcilesRewardLines then creates, resizes or deletes the lines to
     * match, so a line this discount allocates nothing to goes.
     *
     * @return array{0: Collection, 1: int}
     */
    private function processAutomaticRewards(Cart $cart, int $remainingRewardQty, Collection $affectedLines, int $discountTotal): array
    {
        /** @var array<int, array{line: CartLine, quantity: int}> $allocations keyed by spl_object_id() */
        $allocations = [];

        $ownLines = $cart->lines
            ->filter(fn (CartLine $line) => $line->added_by_discount_id == $this->discount->id)
            ->sortBy('id')
            ->values();

        foreach ($ownLines as $line) {
            $quantity = min($line->quantity, $remainingRewardQty);

            $allocations[spl_object_id($line)] = ['line' => $line, 'quantity' => $quantity];

            $remainingRewardQty -= $quantity;
        }

        foreach ($ownLines as $line) {
            $key = spl_object_id($line);

            while ($remainingRewardQty > 0 && $line->purchasable->canBeFulfilledAtQuantity($allocations[$key]['quantity'] + 1)) {
                $allocations[$key]['quantity']++;
                $remainingRewardQty--;
            }
        }

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

                return (bool) $rewardItem->variants()->first()?->canBeFulfilledAtQuantity(1);
            });

            // New lines this run allocates to, keyed by purchasable, so a reward
            // quantity of three opens one line of three rather than three of one.
            $newLines = [];

            while ($remainingRewardQty > 0 && $fulfillableRewards->isNotEmpty()) {
                $selectedRewardItem = $fulfillableRewards->random()->discountable;

                if ($selectedRewardItem instanceof LunarCollection) {
                    $purchasable = $fulfillableCollectionProducts[$selectedRewardItem->id]->random()->variants->first();
                } elseif ($selectedRewardItem instanceof Purchasable) {
                    $purchasable = $selectedRewardItem;
                } else {
                    $purchasable = $selectedRewardItem->variants()->first();
                }

                $remainingRewardQty--;

                if (! $purchasable) {
                    continue;
                }

                $rewardKey = $purchasable->getMorphClass().':'.$purchasable->id;

                // A purchasable one of this discount's lines already holds was
                // raised as far as its stock allows above.
                if (! isset($newLines[$rewardKey]) && $ownLines->contains(
                    fn (CartLine $line) => $line->purchasable_type == $purchasable->getMorphClass() && $line->purchasable_id == $purchasable->id
                )) {
                    continue;
                }

                $allocated = isset($newLines[$rewardKey])
                    ? $allocations[spl_object_id($newLines[$rewardKey])]['quantity']
                    : 0;

                if (! $purchasable->canBeFulfilledAtQuantity($allocated + 1)) {
                    continue;
                }

                if (! isset($newLines[$rewardKey])) {
                    $newLines[$rewardKey] = $cart->lines()->make([
                        'purchasable_type' => $purchasable->getMorphClass(),
                        'purchasable_id' => $purchasable->id,
                        'quantity' => 0,
                        'added_by_discount_id' => $this->discount->id,
                    ]);

                    $allocations[spl_object_id($newLines[$rewardKey])] = ['line' => $newLines[$rewardKey], 'quantity' => 0];
                }

                $allocations[spl_object_id($newLines[$rewardKey])]['quantity']++;
            }
        }

        foreach ($allocations as ['line' => $line, 'quantity' => $quantity]) {
            if ($quantity < 1) {
                continue;
            }

            if (! $line->exists) {
                // Priced here only so this pass's breakdown carries an amount;
                // creating the line makes the calculation run again, and that
                // pass prices it like any other line.
                $line->quantity = $quantity;

                $line = app(Pipeline::class)
                    ->send($line)
                    ->through(app(StepManifest::class)->for(StepList::CartLinePricing)->all())
                    ->thenReturn();

                $line->subTotal = $line->unitPrice->multiply($quantity);
                $line->taxAmount = new PriceValue(0, $cart->currency);
                $line->total = $line->subTotal;
            }

            $lineDiscount = min($line->unitPrice->multiply($quantity)->value, $line->subTotal->value);

            $line->discountTotal = new PriceValue($lineDiscount, $cart->currency);
            $line->subTotalDiscounted = $line->subTotal->subtract($line->discountTotal)->clampToZero();

            $discountTotal += $lineDiscount;

            $affectedLines->push(new DiscountBreakdownLine(
                line: $line,
                quantity: $quantity,
            ));

            if (! $cart->freeItems) {
                $cart->freeItems = collect();
            }

            if (! $cart->freeItems->contains($line->purchasable)) {
                $cart->freeItems->push($line->purchasable);
            }
        }

        return [$affectedLines, $discountTotal];
    }
}
