<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\RecomputesStockRollup;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;

/**
 * Refresh a variant's cached stock rollup from its per-location levels.
 *
 * `on_hand` / `incoming` / `unavailable` are pure sums of the level rows;
 * `committed` and `reserved` are global counters maintained elsewhere, so they
 * are read (not overwritten) when recomputing `available`.
 */
class RecomputeStockRollup implements RecomputesStockRollup
{
    public function execute(ProductVariantContract $variant): ProductVariant
    {
        /** @var ProductVariant $variant */
        $sums = StockLevel::query()
            ->where('product_variant_id', $variant->getKey())
            ->selectRaw('COALESCE(SUM(on_hand), 0) as on_hand')
            ->selectRaw('COALESCE(SUM(incoming), 0) as incoming')
            ->selectRaw('COALESCE(SUM(unavailable), 0) as unavailable')
            ->first();

        $onHand = (int) ($sums->on_hand ?? 0);
        $incoming = (int) ($sums->incoming ?? 0);
        $unavailable = (int) ($sums->unavailable ?? 0);

        $available = $onHand - $variant->stock_committed - $variant->stock_reserved - $unavailable;

        $variant->forceFill([
            'stock_on_hand' => $onHand,
            'stock_incoming' => $incoming,
            'stock_unavailable' => $unavailable,
            'stock_available' => $available,
        ])->save();

        return $variant;
    }
}
