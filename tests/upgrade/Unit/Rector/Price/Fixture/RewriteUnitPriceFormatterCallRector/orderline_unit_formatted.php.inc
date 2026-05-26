<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_formatted(\Lunar\Core\Models\OrderLine $line): ?string
{
    return $line->unit_price->unitFormatted($line->unit_quantity, 'en_GB');
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_formatted(\Lunar\Core\Models\OrderLine $line): ?string
{
    return \app(\Lunar\Core\Pricing\PriceFormatterInterface::class, ['value' => (int) $line->unit_price, 'currency' => $line->resolveCurrency()])->unitFormatted($line->unit_quantity, 'en_GB');
}

?>
