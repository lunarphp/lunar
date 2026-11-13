<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RewriteShippingOptionCollectRector;

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

function collectFalse($price, $taxClass): ShippingOption
{
    return new ShippingOption(name: 'Delivery', description: null, identifier: 'DELIVERY', price: $price, taxClass: $taxClass, collect: false);
}

function readsCollect(ShippingOption $option): bool
{
    return $option->collect;
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RewriteShippingOptionCollectRector;

use Lunar\Core\DataTypes\ShippingOption;

function collectTrue($price, $taxClass): ShippingOption
{
    return new ShippingOption(
        name: 'Collection',
        description: 'Collect in store',
        identifier: 'COLLECT',
        price: $price,
        taxClass: $taxClass,
        pickupLocation: \Lunar\Core\Models\Location::getDefault(),
    );
}

function collectExpression($price, $taxClass, bool $flag): ShippingOption
{
    return new ShippingOption(name: 'Collection', description: null, identifier: 'COLLECT', price: $price, taxClass: $taxClass, pickupLocation: $flag ? \Lunar\Core\Models\Location::getDefault() : null);
}

function collectFalse($price, $taxClass): ShippingOption
{
    return new ShippingOption(name: 'Delivery', description: null, identifier: 'DELIVERY', price: $price, taxClass: $taxClass);
}

function readsCollect(ShippingOption $option): bool
{
    return $option->isPickup();
}

?>
