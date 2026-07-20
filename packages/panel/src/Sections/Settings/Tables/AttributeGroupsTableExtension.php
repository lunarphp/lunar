<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the attribute groups index, registered through
 * the same public TableExtension seam an add-on would use.
 */
class AttributeGroupsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditAttributeGroupAction::class,
            DeleteAttributeGroupAction::class,
        ];
    }
}
