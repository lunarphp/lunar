<?php

namespace Lunar\Panel\Sections\Sales\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row and bulk actions for the discounts index, registered through
 * the same public TableExtension seam an add-on would use.
 */
class DiscountsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditDiscountAction::class,
            DeleteDiscountAction::class,
        ];
    }

    /** @return array<int, class-string> */
    public function bulkActions(): array
    {
        return [
            EndDiscountsBulkAction::class,
            DeleteDiscountsBulkAction::class,
        ];
    }
}
