<?php

namespace Lunar\Panel\Sections\Catalog\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row and bulk actions for the brands index, registered through
 * the same public TableExtension seam an add-on would use.
 */
class BrandsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditBrandAction::class,
            DeleteBrandAction::class,
        ];
    }

    /** @return array<int, class-string> */
    public function bulkActions(): array
    {
        return [
            SetBrandsActiveBulkAction::class,
            SetBrandsDraftBulkAction::class,
        ];
    }
}
