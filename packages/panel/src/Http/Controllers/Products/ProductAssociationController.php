<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Facades\DB;
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

    /**
     * Persist the linking order for one association type. Sort is assigned by
     * the given order; only rows belonging to this product and type are
     * touched, so an id from another product or type is silently ignored.
     */
    public function reorder(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $owned = $product->associations()
            ->where('type', $validated['type'])
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($validated, $owned) {
            $sort = 0;

            foreach ($validated['ids'] as $id) {
                if (! in_array($id, $owned, true)) {
                    continue;
                }

                ProductAssociation::where('id', $id)->update(['sort' => ++$sort]);
            }
        });

        return back()->with('success', __('panel::products.flash_associations_reordered'));
    }

    public function destroy(Product $product, ProductAssociation $association): RedirectResponse
    {
        $association->loadMissing('target');

        $product->dissociate($association->target, (string) $association->type);

        return back()->with('success', __('panel::products.flash_association_removed'));
    }
}
