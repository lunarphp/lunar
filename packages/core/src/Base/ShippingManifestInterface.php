<?php

namespace Lunar\Base;

use Closure;
use Illuminate\Support\Collection;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;

interface ShippingManifestInterface
{
    /**
     * Add a shipping option to the manifest.
     *
     * @return self
     */
    public function addOption(ShippingOption $shippingOption);

    /**
     * Add a collection of shipping options to the manifest.
     *
     * @param  \Lunar\DataTypes\ShippingOption  $shippingOption
     * @return self
     */
    public function addOptions(Collection $shippingOptions);

    /**
     * Remove all shipping options
     *
     * @return self
     */
    public function clearOptions();

    /**
     * Define closure to retrieve shipping option
     */
    public function getOptionUsing(Closure $closure): self;

    /**
     * Return available options for a given cart.
     */
    public function getOptions(Cart $cart): Collection;

    /**
     * Return available option for a given cart by identifier.
     */
    public function getOption(Cart $cart, string $identifier): ?ShippingOption;

    /**
     * Retrieve shipping option for a given cart
     */
    public function getShippingOption(Cart $cart): ?ShippingOption;
}
