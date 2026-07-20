<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the product options index, registered through
 * the same public TableExtension seam an add-on would use.
 */
class ProductOptionsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditProductOptionAction::class,
            DeleteProductOptionAction::class,
        ];
    }
}
