<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\AdjustsStock;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;

/**
 * Apply a stock delta (positive to add, negative to remove) against a variant.
 *
 * Writes an activity-log entry recording the adjustment and the reason.
 * Will be superseded by the upcoming Inventory subsystem; until then this
 * is the canonical seam for editing the `stock` column.
 */
class AdjustStock implements AdjustsStock
{
    public function execute(ProductVariantContract $variant, int $delta, ?string $reason = null): ProductVariant
    {
        /** @var ProductVariant $variant */
        if ($delta === 0) {
            return $variant;
        }

        $newStock = $variant->stock + $delta;

        if ($newStock < 0) {
            throw new ProductActionException('Stock cannot be reduced below zero.');
        }

        $previous = $variant->stock;

        $variant->forceFill(['stock' => $newStock])->save();

        activity()
            ->performedOn($variant)
            ->causedBy(auth()->user())
            ->withProperties([
                'previous' => $previous,
                'new' => $newStock,
                'delta' => $delta,
                'reason' => $reason,
            ])
            ->event('stock-adjusted')
            ->log('stock-adjusted');

        return $variant;
    }
}
