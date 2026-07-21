<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row and bulk actions for the product types index, registered
 * through the same public TableExtension seam an add-on would use.
 */
class ProductTypesTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditProductTypeAction::class,
            DeleteProductTypeAction::class,
        ];
    }

    /** @return array<int, class-string> */
    public function bulkActions(): array
    {
        return [
            SetProductTypesActiveBulkAction::class,
            SetProductTypesDraftBulkAction::class,
        ];
    }
}
