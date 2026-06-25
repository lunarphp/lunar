<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;

interface SyncsStockCommitment
{
    /**
     * Recompute a variant's global `stock_committed` and per-location
     * `StockLevel.committed` from the order book, then refresh the rollup.
     *
     * This is the single home of the canonical committed predicate, shared by
     * the live lifecycle hooks and `lunar:stock:reconcile`.
     */
    public function execute(ProductVariantContract $variant): ProductVariant;
}
