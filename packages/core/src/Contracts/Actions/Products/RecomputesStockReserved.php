<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\ProductVariant;

interface RecomputesStockReserved
{
    /**
     * Recompute the variant's `stock_reserved` rollup as the sum of its active
     * reservations, then refresh the rollup. The counter is reconstructable from
     * the reservation rows, so this is the single home of that sum.
     */
    public function execute(ProductVariant $variant): ProductVariant;
}
