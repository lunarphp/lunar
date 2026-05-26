<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_decimal(\Lunar\Core\Models\OrderLine $line): ?float
{
    return $line->unit_price->unitDecimal();
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_decimal(\Lunar\Core\Models\OrderLine $line): ?float
{
    return $line->unitDecimal('unit_price');
}

?>
