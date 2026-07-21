<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

/**
 * Inline on-hand edit for one location: the posted figure is applied as an
 * adjustment movement (stock is ledger-derived, never a column write), so
 * the audit trail records who set what.
 */
class ProductVariantStockController
{
    public function update(Request $request, Product $product, ProductVariant $productVariant): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', Rule::exists((new Location)->getTable(), 'id')],
            'on_hand' => ['required', 'integer', 'min:0'],
        ]);

        /** @var Location $location */
        $location = Location::query()->findOrFail($validated['location_id']);

        $current = (int) ($productVariant->stockLevels()
            ->where('location_id', $location->id)
            ->value('on_hand') ?? 0);

        $delta = (int) $validated['on_hand'] - $current;

        if ($delta !== 0) {
            $productVariant->adjustStock($location, $delta, StockMovementType::Adjustment);
        }

        return back()->with('success', __('panel::products.flash_stock_updated'));
    }
}
