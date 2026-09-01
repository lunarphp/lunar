<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Checkout\DataObjects\CheckoutAddress;

/**
 * Postcode to address lookup (spec 0011 §A). One verb: type-ahead is
 * deliberately excluded until a second driver exists to validate its shape,
 * and it bills per keystroke. When it arrives it arrives as an additive
 * capability interface, never as extra methods here.
 */
interface AddressLookup
{
    /**
     * Addresses at a postcode. An empty list is a legitimate answer, not an
     * error; vendor failures throw AddressLookupException.
     *
     * @return list<CheckoutAddress>
     */
    public function lookup(string $postcode): array;

    /**
     * Is this driver usable? Projected into the render so the delivery step
     * shows a postcode search only when one can actually answer.
     */
    public function isAvailable(): bool;
}
