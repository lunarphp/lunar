<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;

interface CarrierManifest
{
    /**
     * Register a shipping carrier. Accepts a carrier instance or the class name
     * of a carrier (resolved from the container).
     *
     * @param  ShippingCarrier|class-string<ShippingCarrier>  $carrier
     * @return self
     */
    public function register(ShippingCarrier|string $carrier);

    /**
     * Get all registered carriers, keyed by their carrier key.
     *
     * @return Collection<string, ShippingCarrier>
     */
    public function all(): Collection;

    /**
     * Get a single carrier by key, or null when it is not registered.
     */
    public function get(?string $key): ?ShippingCarrier;
}
