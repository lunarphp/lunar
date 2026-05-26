<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function dynamic_property(\Lunar\Core\Models\OrderLine $line, string $column): mixed
{
    // Rector can't resolve the column statically; the upgrade docs flag this
    // as a manual step.
    return $line->{$column}->unitDecimal($line->unit_quantity);
}

?>
