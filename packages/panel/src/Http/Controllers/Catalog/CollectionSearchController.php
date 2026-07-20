<?php

namespace Lunar\Panel\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Collection;

/**
 * Lightweight collection lookup for relation pickers: id, translated name
 * and ancestry breadcrumb, filtered by a search term. Shared by every
 * catalog screen that attaches collections. `group_id` scopes results to
 * one collection group; `exclude` omits a collection and its whole subtree
 * (the parent picker must not offer a node its own descendants).
 */
class CollectionSearchController
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q')->value();

        $excluded = $request->filled('exclude')
            ? Collection::query()->find($request->integer('exclude'))
            : null;

        $collections = Collection::query()
            // The breadcrumb accessor walks the nested-set ancestors.
            ->with('ancestors')
            ->when($term !== '', function ($query) use ($term) {
                $like = "%{$term}%";

                // The dedicated name column holds a {locale: text} map.
                $query->where('name', 'like', $like);
            })
            ->when(
                $request->filled('group_id'),
                fn ($query) => $query->where('collection_group_id', $request->integer('group_id')),
            )
            ->when($excluded !== null, function ($query) use ($excluded) {
                $query->whereKeyNot($excluded->getKey())
                    ->where(function ($query) use ($excluded) {
                        $query->where('collection_group_id', '!=', $excluded->collection_group_id)
                            ->orWhere('_lft', '<', $excluded->getLft())
                            ->orWhere('_rgt', '>', $excluded->getRgt());
                    });
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
