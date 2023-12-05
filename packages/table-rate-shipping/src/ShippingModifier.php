<?php

namespace Lunar\Shipping;

use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Shipping\DataTransferObjects\ShippingOptionLookup;
use Lunar\Shipping\Facades\Shipping;

class ShippingModifier
{
    public function handle(Cart $cart)
    {
        $shippingMethods = Shipping::shippingMethods($cart)->get();

        $options = Shipping::shippingOptions($cart)->get(
            new ShippingOptionLookup(
                shippingMethods: $shippingMethods
            )
        );

        foreach ($options as $option) {
            ShippingManifest::addOption($option->option);
        }
    }
}
