<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\AdjustsStock;
use Lunar\Core\Contracts\Actions\Products\RecordsStockMovement;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;

/**
 * Apply a manual stock delta (positive to add, negative to remove) against a
 * variant, recording an `Adjustment` movement in the `on_hand` ledger.
 *
 * The manual admin seam over {@see RecordsStockMovement}; defaults to the
 * default location. Manual adjustments are free-form — they may take on_hand
 * negative (the ledger keeps the trail either way).
 */
class AdjustStock implements AdjustsStock
{
    public function __construct(
        protected RecordsStockMovement $recordMovement,
    ) {}

    public function execute(ProductVariantContract $variant, int $delta, ?string $reason = null, ?Location $location = null): ProductVariant
    {
        /** @var ProductVariant $variant */
        if ($delta === 0) {
            return $variant;
        }

        $this->recordMovement->execute(
            $variant,
            $location ?? Location::getDefault(),
            $delta,
            StockMovementType::Adjustment,
            note: $reason,
        );

        return $variant->refresh();
    }
}
