<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductStatus;
use Lunar\Core\Models\Product;

class ProductBulkStatusController
{
    /**
     * Set the status on a selection of products. The status arrives as a
     * route parameter constrained to published/draft; each product is written
     * through the status action so the status event fires per product.
     */
    public function update(Request $request, string $status, UpdatesProductStatus $updatesProductStatus): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists((new Product)->getTable(), 'id')],
        ]);

        Product::query()
            ->whereIn('id', $validated['ids'])
            ->get()
            ->each(fn (Product $product) => $updatesProductStatus->execute($product, $status));

        return back()->with('success', __('panel::products.flash_status_updated'));
    }
}
