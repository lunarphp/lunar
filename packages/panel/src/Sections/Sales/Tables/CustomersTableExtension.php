<?php

namespace Lunar\Panel\Sections\Sales\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the customers index, registered through the same
 * public TableExtension seam an add-on would use.
 */
class CustomersTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditCustomerAction::class,
            DeleteCustomerAction::class,
        ];
    }
}
