<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
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
        $shippingSubTotal = 0;
        $shippingBreakdown = $cart->shippingBreakdown ?: new ShippingBreakdown;

        if ($shippingOption = $this->getShippingOption($cart)) {
            $shippingBreakdown->items->put($shippingOption->getIdentifier(),
                new ShippingBreakdownItem(
                    name: $shippingOption->getName(),
                    identifier: $shippingOption->getIdentifier(),
                    price: $shippingOption->price,
                )
            );

            $shippingSubTotal = $shippingOption->price->value;
            $shippingTotal = $shippingSubTotal;

            $cart->shippingAddress->shippingTotal = new Price($shippingTotal, $cart->currency, 1);
            $cart->shippingAddress->shippingSubTotal = new Price($shippingOption->price->value, $cart->currency, 1);
        }

        $cart->shippingBreakdown = $shippingBreakdown;

        $cart->shippingSubTotal = new Price(
            $shippingBreakdown->items->sum('price.value'),
            $cart->currency,
            1
        );

        return $next($cart);
    }

    private function getShippingOption(Cart $cart)
    {
        if (! $cart->shippingAddress) {
            return null;
        }

        return ShippingManifest::getOptions($cart)->first(function ($option) use ($cart) {
            return $option->getIdentifier() == $cart->shippingAddress->shipping_option;
        });
    }
}
