<?php

namespace Lunar\Shipping\Panel\Tables;

use Lunar\Panel\Tables\TableExtension;

/**
 * First-party row actions for the shipping zones index, registered
 * through the same TableExtension seam another add-on would use.
 */
class ZonesTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function actions(): array
    {
        return [
            EditZoneAction::class,
            DeleteZoneAction::class,
        ];
    }
}
