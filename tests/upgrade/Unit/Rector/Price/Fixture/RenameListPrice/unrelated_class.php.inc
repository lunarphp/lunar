<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RenameListPrice;

final class Catalogue
{
    public ?int $compare_price = null;
}

function leave_unrelated_alone(Catalogue $catalogue): ?int
{
    return $catalogue->compare_price;
}

?>
