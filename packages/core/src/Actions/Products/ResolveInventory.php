<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\DataObjects\VariantInventory;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Models\ProductVariant;

/**
 * Default inventory answer: the variant's own rollup, read through its selling policy.
 */
class ResolveInventory implements ResolvesInventory
{
    public function execute(ProductVariant $variant): VariantInventory
    {
        return new VariantInventory(
            available: match ($variant->selling_policy) {
                SellingPolicy::Always, SellingPolicy::InStock => (int) $variant->stock_available,
                SellingPolicy::InStockOrOnBackorder => (int) $variant->stock_available + (int) $variant->backorder,
            },
            unlimited: $variant->selling_policy === SellingPolicy::Always,
        );
    }
}
