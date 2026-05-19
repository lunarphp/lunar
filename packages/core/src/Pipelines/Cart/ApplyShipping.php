<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;

final class ApplyShipping
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(CartContract): mixed  $next
     */
    public function handle(CartContract $cart, Closure $next): mixed
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
                $cart->shippingAddress->shippingTotal = new Price($shippingOption->price->value, $cart->currency, 1);
                $cart->shippingAddress->shippingSubTotal = new Price($shippingOption->price->value, $cart->currency, 1);
            }
        }

        $cart->shippingBreakdown = $shippingBreakdown;

        return $next($cart);
    }
}
