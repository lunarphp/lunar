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
         * The manifest is scoped rather than per-run, so options from an
         * earlier resolution linger. Drop every table-rate option, then add
         * this resolution's. Options under other identifiers are left alone.
         */
        $methodCodes = ShippingMethod::query()->pluck('code');

        $manifest = ShippingManifest::getFacadeRoot();

        $manifest->options = $manifest->options->reject(
            fn ($existing) => $methodCodes->contains($existing->getIdentifier())
        )->values();

        foreach ($options as $option) {
            ShippingManifest::addOption($option->option);
        }

        return $next($cart);
    }
}
