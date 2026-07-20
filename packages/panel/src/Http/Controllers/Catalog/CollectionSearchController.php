<?php

namespace Lunar\Panel\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Collection;

/**
 * Lightweight collection lookup for relation pickers: id, translated name
 * and ancestry breadcrumb, filtered by a search term. Shared by every
 * catalog screen that attaches collections.
 */
class CollectionSearchController
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q')->value();

        $collections = Collection::query()
            // The breadcrumb accessor walks the nested-set ancestors.
            ->with('ancestors')
            ->when($term !== '', function ($query) use ($term) {
                $like = "%{$term}%";

                // The dedicated name column holds a {locale: text} map.
                $query->where('name', 'like', $like);
            })
            ->limit(20)
            ->get()
            ->map(fn (Collection $collection) => [
                'id' => $collection->id,
                'name' => $collection->translate('name'),
                'breadcrumb' => $collection->breadcrumb->implode(' > '),
            ])
            ->values();

        return response()->json(['data' => $collections]);
    }
}
