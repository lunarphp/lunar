<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;

final class ApplyShipping
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        $shippingBreakdown = $cart->shippingBreakdown ?: new ShippingBreakdown;

        $shippingOption = $cart->shippingOptionOverride ?: ShippingManifest::getShippingOption($cart);

        if ($shippingOption) {
            $shippingBreakdown->items->put(
                $shippingOption->getIdentifier(),
                new ShippingBreakdownItem(
                    name: $shippingOption->getName(),
                    identifier: $shippingOption->getIdentifier(),
                    price: $shippingOption->price,
                )
            );
        }

        $cart->shippingBreakdown = $shippingBreakdown;

        return $next($cart);
    }
}
