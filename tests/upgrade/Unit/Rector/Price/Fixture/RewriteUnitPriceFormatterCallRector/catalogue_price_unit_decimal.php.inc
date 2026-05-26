<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_decimal(\Lunar\Core\Models\Price $catalogPrice): ?float
{
    return $catalogPrice->price->unitDecimal();
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_decimal(\Lunar\Core\Models\Price $catalogPrice): ?float
{
    return $catalogPrice->unitDecimal('price');
}

?>
