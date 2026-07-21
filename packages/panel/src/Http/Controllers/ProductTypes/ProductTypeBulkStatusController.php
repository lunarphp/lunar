<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\ProductTypes\UpdatesProductType;
use Lunar\Core\Models\ProductType;

class ProductTypeBulkStatusController
{
    /**
     * Set the status on a selection of product types. The status arrives as a
     * route parameter constrained to the valid states; each type is written
     * through the update action so events and activity logging fire as a
     * single edit would.
     */
    public function update(Request $request, string $status, UpdatesProductType $updatesProductType): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists((new ProductType)->getTable(), 'id')],
        ]);

        ProductType::query()
            ->whereIn('id', $validated['ids'])
            ->get()
            ->each(fn (ProductType $productType) => $updatesProductType->execute($productType, ['status' => $status]));

        return back()->with('success', __('panel::product-types.flash_status_updated'));
    }
}
