<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

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
                $cart->shippingAddress->shippingTotal = new PriceValue($shippingOption->price->value, $cart->currency);
                $cart->shippingAddress->shippingSubTotal = new PriceValue($shippingOption->price->value, $cart->currency);
            }
        }

        $cart->shippingBreakdown = $shippingBreakdown;

        return $next($cart);
    }
}
