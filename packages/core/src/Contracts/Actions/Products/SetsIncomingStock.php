<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;

interface SetsIncomingStock
{
    /**
     * Set the stock a variant is expecting at a location, creating the level at
     * zero if absent, then refresh the variant rollup — all in one transaction.
     *
     * The quantity is absolute, not a delta, and must not be negative.
     */
    public function execute(ProductVariant $variant, Location $location, int $quantity): StockLevel;
}
