<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RenameListPrice;

function list_prices(\Lunar\Core\Models\Price $price): array
{
    return [
        $price->compare_price,
        $price->compare_price->value,
        $price->compare_price->decimal(),
        $price->compare_price->formatted(),
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RenameListPrice;

function list_prices(\Lunar\Core\Models\Price $price): array
{
    return [
        $price->list_price,
        $price->list_price,
        $price->decimal('list_price'),
        $price->format('list_price'),
    ];
}

?>
