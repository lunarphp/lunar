<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Shipping\Fixture\RenameShippingOptionCollectArgRector;

use Lunar\Core\DataTypes\ShippingOption;

class NotAShippingOption
{
    public function __construct(public bool $collect = false) {}
}

function untouched($price, $taxClass): void
{
    new NotAShippingOption(collect: true);
    new ShippingOption(name: 'Pickup', description: null, identifier: 'PICKUP', price: $price, taxClass: $taxClass, pickup: true);
}
