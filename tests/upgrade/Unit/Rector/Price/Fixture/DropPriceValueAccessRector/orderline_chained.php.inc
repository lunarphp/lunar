<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\DropPriceValueAccessRector;

function unit_price_value(\Lunar\Core\Models\OrderLine $line): int
{
    return $line->unit_price->value + $line->sub_total->value;
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\DropPriceValueAccessRector;

function unit_price_value(\Lunar\Core\Models\OrderLine $line): int
{
    return $line->unit_price + $line->sub_total;
}

?>
