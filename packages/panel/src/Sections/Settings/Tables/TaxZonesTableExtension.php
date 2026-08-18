<?php

namespace Lunar\Panel\Sections\Settings\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the tax zones index, registered through the
 * same public TableExtension seam an add-on would use.
 */
class TaxZonesTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditTaxZoneAction::class,
            DeleteTaxZoneAction::class,
        ];
    }
}
