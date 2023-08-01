<?php

namespace Lunar\Pipelines\Cart\Concerns;

use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;

trait HasShippingOption
{
    private function getShippingOption(Cart $cart): ?ShippingOption
    {
        return ShippingManifest::getShippingOption($cart);
    }
}
