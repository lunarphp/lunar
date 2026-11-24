<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RewriteShippingOptionCollectRector;

use Lunar\Core\DataTypes\ShippingOption;

function positionalTrue($price, $taxClass): ShippingOption
{
    return new ShippingOption('Collection', null, 'COLLECT', $price, $taxClass, null, null, true);
}

function positionalFalseKeepsMeta($price, $taxClass): ShippingOption
{
    return new ShippingOption('Delivery', null, 'DELIVERY', $price, $taxClass, null, null, false, ['zone' => 'UK']);
}

function positionalExpression($price, $taxClass, bool $flag): ShippingOption
{
    return new ShippingOption('Collection', null, 'COLLECT', $price, $taxClass, null, null, $flag);
}

function withoutCollect($price, $taxClass): ShippingOption
{
    return new ShippingOption('Delivery', null, 'DELIVERY', $price, $taxClass, null, 'Next day');
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RewriteShippingOptionCollectRector;

use Lunar\Core\DataTypes\ShippingOption;

function positionalTrue($price, $taxClass): ShippingOption
{
    return new ShippingOption('Collection', null, 'COLLECT', $price, $taxClass, null, null, \Lunar\Core\Models\Location::getDefault());
}

function positionalFalseKeepsMeta($price, $taxClass): ShippingOption
{
    return new ShippingOption('Delivery', null, 'DELIVERY', $price, $taxClass, null, null, null, ['zone' => 'UK']);
}

function positionalExpression($price, $taxClass, bool $flag): ShippingOption
{
    return new ShippingOption('Collection', null, 'COLLECT', $price, $taxClass, null, null, $flag ? \Lunar\Core\Models\Location::getDefault() : null);
}

function withoutCollect($price, $taxClass): ShippingOption
{
    return new ShippingOption('Delivery', null, 'DELIVERY', $price, $taxClass, null, 'Next day');
}

?>
