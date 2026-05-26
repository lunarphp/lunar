<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_formatted(\Lunar\Core\Models\OrderLine $line): ?string
{
    return $line->unit_price->unitFormatted('en_GB');
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_formatted(\Lunar\Core\Models\OrderLine $line): ?string
{
    return $line->unitFormat('unit_price', 'en_GB');
}

?>
