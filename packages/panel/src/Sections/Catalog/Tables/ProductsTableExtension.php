<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row and bulk actions for the products index, registered
 * through the same public TableExtension seam an add-on would use.
 */
class ProductsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditProductAction::class,
            DeleteProductAction::class,
        ];
    }

    /** @return array<int, class-string> */
    public function bulkActions(): array
    {
        return [
            SetProductsPublishedBulkAction::class,
            SetProductsDraftBulkAction::class,
        ];
    }
}
