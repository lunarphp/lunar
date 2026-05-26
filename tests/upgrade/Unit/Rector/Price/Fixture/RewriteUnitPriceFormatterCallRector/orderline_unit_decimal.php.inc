<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_decimal(\Lunar\Core\Models\OrderLine $line): ?float
{
    return $line->unit_price->unitDecimal($line->unit_quantity);
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_decimal(\Lunar\Core\Models\OrderLine $line): ?float
{
    return \app(\Lunar\Core\Pricing\PriceFormatterInterface::class, ['value' => (int) $line->unit_price, 'currency' => $line->resolveCurrency()])->unitDecimal($line->unit_quantity);
}

?>
