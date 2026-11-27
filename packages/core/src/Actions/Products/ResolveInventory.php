<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\DataObjects\VariantInventory;
use Lunar\Core\Models\ProductVariant;

/**
 * Default inventory answer: the variant's own rollup, read through its selling
 * flags — `continue_selling` ignores stock, `sell_against_incoming` counts the
 * incoming bucket as sellable.
 */
class ResolveInventory implements ResolvesInventory
{
    public function execute(ProductVariant $variant): VariantInventory
    {
        return new VariantInventory(
            available: (int) $variant->stock_available
                + ($variant->sell_against_incoming ? (int) $variant->stock_incoming : 0),
            unlimited: (bool) $variant->continue_selling,
        );
    }
}
