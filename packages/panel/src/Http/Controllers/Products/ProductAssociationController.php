<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Panel\Http\Requests\Products\ProductAssociationRequest;

class ProductAssociationController
{
    /**
     * Link the picked products with the given association type, through the
     * model's associate() verb. Already-linked targets are skipped so a
     * repeated pick never duplicates a row.
     */
    public function store(ProductAssociationRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $existing = $product->associations()
            ->where('type', $validated['type'])
            ->pluck('product_target_id');

        $targets = Product::query()
            ->whereIn('id', collect($validated['product_ids'])->reject(
                fn (int $id) => $id === $product->id || $existing->contains($id)
            ))
            ->get();

        foreach ($targets as $target) {
            $product->associate($target, $validated['type']);
        }

        return back()->with('success', __('panel::products.flash_associations_added'));
    }

    public function destroy(Product $product, ProductAssociation $association): RedirectResponse
    {
        $association->loadMissing('target');

        $product->dissociate($association->target, (string) $association->type);

        return back()->with('success', __('panel::products.flash_association_removed'));
    }
}
