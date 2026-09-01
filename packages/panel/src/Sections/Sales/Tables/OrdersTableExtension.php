<?php

namespace Lunar\Panel\Sections\Sales\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the orders index, registered through the same
 * public TableExtension seam an add-on would use. Bulk actions (capture,
 * cancel) land alongside their single-order endpoints in a later slice.
 */
class OrdersTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            ViewOrderAction::class,
        ];
    }
}
