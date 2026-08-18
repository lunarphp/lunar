<?php

namespace Lunar\Panel\Http\Controllers\Collections;

use Illuminate\Http\JsonResponse;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Http\Controllers\Concerns\PresentsCollectionRows;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

/**
 * Serves one collection's immediate children as tree rows, letting the
 * collections index lazy-load a subtree when its row is expanded rather than
 * shipping the whole tree up front.
 */
class CollectionChildrenController
{
    use PresentsCollectionRows;
    use ResolvesTableExtensions;

    public function index(Collection $collection): JsonResponse
    {
        $resolver = $this->resolveTable('collections.index');

        $children = $collection->children()
            ->withCount('products')
            ->with('thumbnail')
            ->defaultOrder()
            ->get()
            ->map(fn (Collection $child) => $this->collectionRow($child, $resolver));

        return response()->json(['data' => $children]);
    }
}
