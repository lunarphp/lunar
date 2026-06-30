<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\ProductVariant;

interface RecomputesStockRollup
{
    /**
     * Recompute the variant's cached rollup from its stock levels.
     *
     * Sums `on_hand` / `incoming` / `unavailable` across the variant's levels
     * and recomputes `stock_available`. The `stock_committed` and `stock_reserved`
     * counters are maintained by their own seams and left untouched here.
     */
    public function execute(ProductVariant $variant): ProductVariant;
}
