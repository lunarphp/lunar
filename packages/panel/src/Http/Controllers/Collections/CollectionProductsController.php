<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;

/**
 * Product curation is an immediate sub-resource (the media pattern), not a
 * drafted field: curated sets can run to hundreds of products, so membership
 * is paginated and persisted per operation. Ordering lives on the pivot's
 * `position` and only applies while the collection's sort rule is `custom`.
 */
class CollectionProductsController
{
    public function attach(Request $request, Collection $collection): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists((new Product)->getTable(), 'id')],
        ]);

        $existing = $collection->products()->allRelatedIds();
        $ids = collect($validated['ids'])->map(fn (mixed $id) => (int) $id)->diff($existing)->values();

        // Appended at the end of the position sequence, in the picked order.
        $position = (int) $collection->products()->max('position');

        $collection->products()->attach(
            $ids->mapWithKeys(fn (int $id, int $index) => [$id => ['position' => $position + $index + 1]])->all()
        );

        return back()->with('success', __('panel::collections.flash_products_added'));
    }

    public function detach(Collection $collection, Product $product): RedirectResponse
    {
        $collection->products()->detach($product);

        return back()->with('success', __('panel::collections.flash_products_removed'));
    }

    public function reorder(Request $request, Collection $collection): RedirectResponse
    {
        if ($collection->sort !== 'custom') {
            return back()->with('error', __('panel::collections.flash_products_reorder_not_custom'));
        }

        $validated = $request->validate([
            // The ordered ids of the current page; positions rewrite inside
            // that page's window so other pages keep their order.
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ]);

        $offset = (int) ($validated['offset'] ?? 0);

        foreach (array_values($validated['ids']) as $index => $id) {
            $collection->products()->updateExistingPivot((int) $id, [
                'position' => $offset + $index + 1,
            ]);
        }

        return back()->with('success', __('panel::collections.flash_products_reordered'));
    }
}
