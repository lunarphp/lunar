<?php

namespace Lunar\Core\DiscountTypes\Concerns;

use Illuminate\Support\Collection as SupportCollection;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Collection;

/**
 * Narrows a cart's lines to those a discount targets, honouring the limitation
 * and exclusion buckets for collections, brands, products and variants.
 *
 * A trait rather than an intermediate base class: cart-level types (shipping
 * discounts, for one) extend AbstractDiscountType too and must not inherit line
 * targeting they never use.
 */
trait TargetsCartLines
{
    /**
     * Return the eligible lines for the discount.
     */
    protected function getEligibleLines(Cart $cart): SupportCollection
    {
        $collectionIds = $this->discount->collections->where('pivot.type', 'limitation')->pluck('id');
        $collectionExclusionIds = $this->discount->collections->where('pivot.type', 'exclusion')->pluck('id');

        $brandIds = $this->discount->brands->where('pivot.type', 'limitation')->pluck('id');
        $brandExclusionIds = $this->discount->brands->where('pivot.type', 'exclusion')->pluck('id');

        $productIds = $this->discount->discountableLimitations
            ->reject(fn ($limitation) => ! $limitation->discountable)
            ->map(fn ($limitation) => get_class($limitation->discountable).'::'.$limitation->discountable->id);

        $productExclusionIds = $this->discount->discountableExclusions
            ->reject(fn ($limitation) => ! $limitation->discountable)
            ->map(fn ($limitation) => get_class($limitation->discountable).'::'.$limitation->discountable->id);

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
                return $line->purchasable->product()->whereHas('collections', function ($query) use ($collectionExclusionIds) {
                    $query->whereIn((new Collection)->getTable().'.id', $collectionExclusionIds);
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
}
