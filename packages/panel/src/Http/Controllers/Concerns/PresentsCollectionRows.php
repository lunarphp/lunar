<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Illuminate\Support\Collection as SupportCollection;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Tables\Resolvers\TableExtensionResolver;

/**
 * Maps a loaded collection to the tree-row array shape shared by the index
 * page and the lazy children endpoint, so both surfaces stay identical.
 */
trait PresentsCollectionRows
{
    /**
     * $matchedIds is only set while filtering; a null value flags every row as
     * matched. `children` starts empty — the panel fetches it from
     * `children_url` when the row is expanded.
     *
     * @param  SupportCollection<int, int>|null  $matchedIds
     * @return array<string, mixed>
     */
    protected function collectionRow(Collection $collection, TableExtensionResolver $resolver, ?SupportCollection $matchedIds = null): array
    {
        return [
            'id' => $collection->id,
            'parent_id' => $collection->parent_id,
            'group_id' => $collection->collection_group_id,
            'name' => $collection->translate('name'),
            'handle' => $collection->handle,
            'thumbnail' => $collection->thumbnail?->getAvailableUrl(['small']),
            'short_description' => $collection->translate('short_description'),
            'status' => $collection->status->getValue(),
            'status_label' => $collection->status->label(),
            'products_count' => (int) $collection->getAttribute('products_count'),
            // Nested-set bounds carry the subtree size without another query.
            'descendants_count' => (int) (($collection->getRgt() - $collection->getLft() - 1) / 2),
            'matched' => $matchedIds === null || $matchedIds->has($collection->id),
            'edit_url' => route('panel.collections.edit', $collection),
            'children_url' => route('panel.collections.children', $collection),
            '_actions' => $resolver->resolveRowActionUrls($collection),
            'children' => [],
        ];
    }
}
