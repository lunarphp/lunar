<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\RecomputesStockReserved;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockRollup;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockReservation;

/**
 * Refresh a variant's `stock_reserved` rollup from its active reservations,
 * then recompute `available`.
 */
class RecomputeStockReserved implements RecomputesStockReserved
{
    public function __construct(
        protected RecomputesStockRollup $recomputeRollup,
    ) {}

    public function execute(ProductVariantContract $variant): ProductVariant
    {
        /** @var ProductVariant $variant */
        $reserved = (int) StockReservation::query()
            ->where('product_variant_id', $variant->getKey())
            ->active()
            ->sum('quantity');

        $variant->forceFill(['stock_reserved' => $reserved])->save();

        $this->recomputeRollup->execute($variant);

        return $variant;
    }
}
