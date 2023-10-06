<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdownLine;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Collection;

class AmountOff extends AbstractDiscountType
{
    /**
     * Return the name of the discount.
     */
    public function getName(): string
    {
        return 'Amount off';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function apply(Cart $cart): Cart
    {
        $data = $this->discount->data;

        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        if ($data['fixed_value']) {
            return $this->applyFixedValue(
                values: $data['fixed_values'],
                cart: $cart,
            );
        }

        return $this->applyPercentage(
            value: $data['percentage'],
            cart: $cart
        );
    }

    /**
     * Apply fixed value discount
     */
    private function applyFixedValue(array $values, Cart $cart): Cart
    {
        $currency = $cart->currency;

        $value = (int) bcmul($values[$currency->code] ?? 0, $currency->factor);

        $lines = $this->getEligibleLines($cart);
        $linesSubtotal = $lines->sum(function ($line) {
            return ($line->subTotalDiscounted ?? $line->subTotal)->value;
        });

        if (! $value || $linesSubtotal < $value) {
            return $cart;
        }

        $divisionalAmount = $value / $linesSubtotal;

        $remaining = $value;

        $affectedLines = collect();

        foreach ($lines as $line) {
            $subTotal = ($line->subTotalDiscounted ?? $line->subTotal)->value;
            $amount = (int) floor($subTotal * $divisionalAmount);

            if ($amount > $subTotal) {
                $amount = $subTotal;
            }

            // If this line already has a greater discount value
            // don't add this one as they already have a better deal.
            if ($line->discountTotal->value > $amount) {
                continue;
            }

            $remaining -= $amount;

            $line->discountTotal = new Price(
                $amount,
                $cart->currency,
                1
            );

            $line->subTotalDiscounted = new Price(
                $line->subTotal->value - $amount,
                $cart->currency,
                1
            );

            $affectedLines->push(new DiscountBreakdownLine(
                line: $line,
                quantity: $line->quantity
            ));
        }

        // Do we have an amount left over? if so, grab the first line that has
        // enough left to apply the remaining too.
        if ($remaining) {
            // prioritise sharing the remaining over eligible lines
            $lines->filter(function ($line) {
                return $line->subTotalDiscounted->value > 0;
            })
                ->each(function($line) use ($affectedLines, $cart, &$remaining) {
                    if ($remaining <= 0) {
                        return;
                    }
                    
                    $amountAvailable = min($line->subTotalDiscounted->value, $remaining);
                    $remaining -= $amountAvailable;

                    $newDiscountTotal = $line->discountTotal->value + $amountAvailable;
    
                    $line->discountTotal = new Price(
                        $newDiscountTotal,
                        $cart->currency,
                        1
                    );
    
                    $line->subTotalDiscounted = new Price(
                        $line->subTotal->value - $newDiscountTotal,
                        $cart->currency,
                        1
                    );
    
                    if (! $affectedLines->first(function ($breakdownLine) use ($line) {
                        return $breakdownLine->line == $line;
                    })) {
                        $affectedLines->push(new DiscountBreakdownLine(
                            line: $line,
                            quantity: $line->quantity
                        ));
                    }
                });
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            discount: $this->discount,
            lines: $affectedLines,
            price: new Price($value - $remaining, $cart->currency, 1)
        ));

        return $cart;
    }

    /**
     * Return the eligible lines for the discount.
     */
    protected function getEligibleLines(Cart $cart): \Illuminate\Support\Collection
    {
        $collectionIds = $this->discount->collections->where('pivot.type', 'limitation')->pluck('id');
        $collectionExclusionIds = $this->discount->collections->where('pivot.type', 'exclusion')->pluck('id');
        
        $brandIds = $this->discount->brands->where('pivot.type', 'limitation')->pluck('id');
        $brandExclusionIds = $this->discount->brands->where('pivot.type', 'exclusion')->pluck('id');
        
        $productIds = $this->discount->purchasableLimitations
            ->reject(fn ($limitation) => ! $limitation->purchasable)
            ->map(fn ($limitation) => get_class($limitation->purchasable).'::'.$limitation->purchasable->id);
            
        $productExclusionIds = $this->discount->purchasableExclusions
            ->reject(fn ($limitation) => ! $limitation->purchasable)
            ->map(fn ($limitation) => get_class($limitation->purchasable).'::'.$limitation->purchasable->id);

        $lines = $cart->lines;

        if ($collectionIds->count()) {
            $lines = $lines->filter(function ($line) use ($collectionIds) {
                return $line->purchasable->product()->whereHas('collections', function ($query) use ($collectionIds) {
                    $query->whereIn((new Collection)->getTable().'.id', $collectionIds);
                })->exists();
            });
        }
        
        if ($collectionExclusionIds->count()) {
            $lines = $lines->reject(function ($line) use ($collectionExclusionIds) {
                return $line->purchasable->product()->whereHas('collections', function ($query) use ($collectionIds) {
                    $query->whereIn((new Collection)->getTable().'.id', $collectionIds);
                })->exists();
            });
        }

        if ($brandIds->count()) {
            $lines = $lines->reject(function ($line) use ($brandIds) {
                return ! $brandIds->contains($line->purchasable->product->brand_id);
            });
        }
        
        if ($brandExclusionIds->count()) {
            $lines = $lines->reject(function ($line) use ($brandExclusionIds) {
                return $brandExclusionIds->contains($line->purchasable->product->brand_id);
            });
        }

        if ($productIds->count()) {
            $lines = $lines->filter(function ($line) use ($productIds) {
                return $productIds->contains(get_class($line->purchasable).'::'.$line->purchasable->id) || $productIds->contains(get_class($line->purchasable->product).'::'.$line->purchasable->product->id);
            });
        }
        
        if ($productExclusionIds->count()) {
            $lines = $lines->reject(function ($line) use ($productExclusionIds) {
                return $productExclusionIds->contains(get_class($line->purchasable).'::'.$line->purchasable->id) || $productExclusionIds->contains(get_class($line->purchasable->product).'::'.$line->purchasable->product->id);
            });
        }

        return $lines;
    }

    /**
     * Apply the percentage to the cart line.
     *
     * @param  int  $value
     * @param  CartLine  $cartLine
     * @return CartLine
     */
    private function applyPercentage($value, $cart): Cart
    {
        $lines = $this->getEligibleLines($cart);

        $affectedLines = collect();
        $totalDiscount = 0;

        foreach ($lines as $line) {
            $subTotal = $line->subTotal->value;
            $subTotalDiscounted = $line->subTotalDiscounted?->value ?: 0;

            if ($subTotalDiscounted) {
                $subTotal = $subTotalDiscounted;
            }

            $amount = (int) round($subTotal * ($value / 100));

            // If this line already has a greater discount value
            // don't add this one as they already have a better deal.
            if ($line->discountTotal->value > $amount) {
                continue;
            }

            $totalDiscount += $amount;

            $line->discountTotal = new Price(
                $amount,
                $cart->currency,
                1
            );

            $line->subTotalDiscounted = new Price(
                $subTotal - $amount,
                $cart->currency,
                1
            );

            $affectedLines->push(new DiscountBreakdownLine(
                line: $line,
                quantity: $line->quantity
            ));
        }

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        if ($totalDiscount <= 0) {
            return $cart;
        }

        $cart->discounts->push($this);

        $this->addDiscountBreakdown($cart, new DiscountBreakdown(
            discount: $this->discount,
            lines: $affectedLines,
            price: new Price($totalDiscount, $cart->currency, 1)
        ));

        return $cart;
    }
}
