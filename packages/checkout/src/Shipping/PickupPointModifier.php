<?php

namespace Lunar\Checkout\Shipping;

use Closure;
use Lunar\Checkout\Support\PickupPoints;
use Lunar\Core\Contracts\ShippingManifest;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;
use Lunar\Core\Modifiers\ShippingModifier;

/**
 * Copies the cart's chosen collection point onto every collect option's meta
 * (spec 0013 §C), so core's CreateShippingLine stamps it onto the shipping
 * order line with no order write of our own. Acts after the rest of the
 * pipeline has pushed its options, so provider boot order does not matter.
 */
class PickupPointModifier extends ShippingModifier
{
    public function handle(Cart $cart, Closure $next): mixed
    {
        $result = $next($cart);

        $point = $cart->meta[PickupPoints::POINT_KEY] ?? null;

        if (! is_array($point) || ! isset($point['handle'])) {
            return $result;
        }

        foreach (app(ShippingManifest::class)->options as $option) {
            if ($option instanceof ShippingOption && $option->collect) {
                $option->meta = array_merge($option->meta ?? [], [PickupPoints::POINT_KEY => $point]);
            }
        }

        return $result;
    }
}
