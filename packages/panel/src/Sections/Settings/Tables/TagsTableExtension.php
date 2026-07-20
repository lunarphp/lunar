<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the tags index, registered through the same
 * public TableExtension seam an add-on would use. Tags are edited inline, so
 * the only first-party row action is delete.
 */
class TagsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            DeleteTagAction::class,
        ];
    }
}
