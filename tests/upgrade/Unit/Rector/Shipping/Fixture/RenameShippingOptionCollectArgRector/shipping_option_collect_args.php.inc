<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RenameShippingOptionCollectArgRector;

use Lunar\Core\DataTypes\ShippingOption;

function collectTrue($price, $taxClass): ShippingOption
{
    return new ShippingOption(
        name: 'Collection',
        description: 'Collect in store',
        identifier: 'COLLECT',
        price: $price,
        taxClass: $taxClass,
        collect: true,
    );
}

function collectExpression($price, $taxClass, bool $flag): ShippingOption
{
    return new ShippingOption(name: 'Collection', description: null, identifier: 'COLLECT', price: $price, taxClass: $taxClass, collect: $flag);
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RenameShippingOptionCollectArgRector;

use Lunar\Core\DataTypes\ShippingOption;

function collectTrue($price, $taxClass): ShippingOption
{
    return new ShippingOption(
        name: 'Collection',
        description: 'Collect in store',
        identifier: 'COLLECT',
        price: $price,
        taxClass: $taxClass,
        pickup: true,
    );
}

function collectExpression($price, $taxClass, bool $flag): ShippingOption
{
    return new ShippingOption(name: 'Collection', description: null, identifier: 'COLLECT', price: $price, taxClass: $taxClass, pickup: $flag);
}

?>
