<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function order_line_unit_price(\Lunar\Core\Models\OrderLine $line): array
{
    return [$line->unit_price->unitDecimal(), $line->unit_price->unitFormatted()];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function order_line_unit_price(\Lunar\Core\Models\OrderLine $line): array
{
    return [$line->unitDecimal('unit_price'), $line->unitFormat('unit_price')];
}

?>
