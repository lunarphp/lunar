<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the collections tree, registered through the
 * same public TableExtension seam an add-on would use — tree rows resolve
 * and render row actions exactly like table rows. No bulk actions: the tree
 * has no row selection.
 */
class CollectionsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditCollectionAction::class,
            AddChildCollectionAction::class,
            DeleteCollectionAction::class,
        ];
    }
}
