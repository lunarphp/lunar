<?php

namespace Lunar\Shipping;

use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Shipping\DataTransferObjects\ShippingOptionLookup;
use Lunar\Shipping\Facades\Shipping;

class ShippingModifier
{
    public function handle(Cart $cart, \Closure $next)
    {
        $shippingRates = Shipping::shippingRates($cart)->get();

        $options = Shipping::shippingOptions($cart)->get(
            new ShippingOptionLookup(
                shippingRates: $shippingRates
            )
        );

        foreach ($options as $option) {
            ShippingManifest::addOption($option->option);
        }

        return $next($cart);
    }
}
