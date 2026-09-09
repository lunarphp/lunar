<?php

namespace Lunar\Shipping;

use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Shipping\DataTransferObjects\ShippingOptionLookup;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;

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

        /*
         * The manifest is a singleton that only ever accumulates, so an option
         * this modifier added for an earlier address (or an earlier cart on a
         * long-lived worker) would survive a resolution that no longer offers
         * it. Drop every table-rate option not offered this time; options
         * other code added under identifiers that are not shipping method
         * codes are left alone.
         */
        $offered = $options->map(fn ($option) => $option->option->getIdentifier());
        $methodCodes = ShippingMethod::query()->pluck('code');

        ShippingManifest::getFacadeRoot()->options = ShippingManifest::getFacadeRoot()->options->reject(
            fn ($existing) => $methodCodes->contains($existing->getIdentifier())
                && ! $offered->contains($existing->getIdentifier())
        )->values();

        foreach ($options as $option) {
            ShippingManifest::addOption($option->option);
        }

        return $next($cart);
    }
}
