<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\SellingPolicy\Fixture\RenameSellingPolicy;

function policies(\Lunar\Core\Models\ProductVariant $variant, \Lunar\Core\Models\OrderLine $line): array
{
    return [
        $variant->purchasable,
        $line->purchasable,
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\SellingPolicy\Fixture\RenameSellingPolicy;

function policies(\Lunar\Core\Models\ProductVariant $variant, \Lunar\Core\Models\OrderLine $line): array
{
    return [
        $variant->selling_policy,
        $line->purchasable,
    ];
}

?>
