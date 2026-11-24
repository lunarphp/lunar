<?php

namespace Lunar\Core\Validation\Cart;

use Lunar\Core\Models\Location;
use Lunar\Core\Validation\BaseValidator;

/**
 * A cart can only be collected from a location that accepts pickup.
 */
class CartPickupLocation extends BaseValidator
{
    public function validate(): bool
    {
        $location = $this->parameters['location'] ?? null;

        if (! $location instanceof Location || $location->accepts_pickup) {
            return $this->pass();
        }

        return $this->fail('cart', __('lunar::exceptions.carts.pickup_location_unavailable'));
    }
}
