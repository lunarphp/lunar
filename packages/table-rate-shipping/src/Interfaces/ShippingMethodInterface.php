<?php

namespace Lunar\Shipping\Interfaces;

use Lunar\DataTypes\ShippingOption;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Models\ShippingMethod;

interface ShippingMethodInterface
{
    /**
     * Return the name of the shipping method.
     */
    public function name(): string;

    /**
     * Return the description of the shipping method.
     */
    public function description(): string;

    /**
     * Set the context for the driver.
     */
    public function on(ShippingMethod $shippingMethod): self;

    /**
     * Return the shipping option price.
     *
     * @return ShippingOption
     */
    public function resolve(ShippingOptionRequest $shippingOptionRequest): ?ShippingOption;
}
