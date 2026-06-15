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
     * Replace the entire carrier set — e.g. to drop the batteries-included
     * carriers and register your own from a service provider.
     *
     * @param  iterable<ShippingCarrier|class-string<ShippingCarrier>>  $carriers
     * @return self
     */
    public function set(iterable $carriers);

    /**
     * Remove one or more registered carriers by key — e.g. a default you do
     * not ship with.
     *
     * @return self
     */
    public function forget(string ...$keys);

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
