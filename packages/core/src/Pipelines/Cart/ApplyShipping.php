<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdownItem;

class ApplyShipping
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(Cart):mixed  $next
     */
    public function handle(Cart $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $shippingBreakdown = new ShippingBreakdown;

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

            if ($cart->shippingAddress && ! $cart->shippingBreakdown) {
                $cart->shippingAddress->shippingTotal = $shippingOption->price;
                $cart->shippingAddress->shippingSubTotal = $shippingOption->price;
            }
        }

        $cart->shippingBreakdown = $shippingBreakdown;

        return $next($cart);
    }
}
