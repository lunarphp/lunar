<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Http\Requests\Products\ProductPriceRequest;

/**
 * Immediate price-row endpoints for a variant: base rows (one per currency),
 * customer-group overrides and quantity-break tiers are all rows on the same
 * prices table, distinguished by their dimensions. Rows persist per
 * operation — pricing has row identity, so it follows the URL-slugs
 * precedent rather than the draft.
 */
class ProductPriceController
{
    public function store(ProductPriceRequest $request, Product $product, ProductVariant $productVariant): RedirectResponse
    {
        $productVariant->prices()->create($request->validated());

        return back()->with('success', __('panel::pricing.flash_created'));
    }

    public function update(ProductPriceRequest $request, Product $product, ProductVariant $productVariant, Price $price): RedirectResponse
    {
        $price->update($request->validated());

        return back()->with('success', __('panel::pricing.flash_updated'));
    }

    public function destroy(Product $product, ProductVariant $productVariant, Price $price): RedirectResponse
    {
        $price->delete();

        return back()->with('success', __('panel::pricing.flash_deleted'));
    }
}
