<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_formatted(\Lunar\Core\Models\Price $catalogPrice): ?string
{
    return $catalogPrice->compare_price->unitFormatted('en_GB');
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function unit_formatted(\Lunar\Core\Models\Price $catalogPrice): ?string
{
    return $catalogPrice->unitFormat('compare_price', 'en_GB');
}

?>
