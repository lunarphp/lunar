<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;

interface AdjustsStock
{
    /**
     * Apply a manual stock delta against a variant at a location, recording an
     * `Adjustment` movement in the ledger. Defaults to the default location.
     */
    public function execute(ProductVariantContract $variant, int $delta, ?string $reason = null, ?Location $location = null): ProductVariant;
}
