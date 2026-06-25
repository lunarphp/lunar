<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockMovement;

interface RecordsStockMovement
{
    /**
     * Append a signed `on_hand` movement for a variant at a location, creating
     * the level at zero if absent, then refresh the variant rollup — all in one
     * transaction.
     */
    public function execute(
        ProductVariantContract $variant,
        Location $location,
        int $quantity,
        StockMovementType $type,
        ?Model $source = null,
        ?string $note = null,
    ): StockMovement;
}
