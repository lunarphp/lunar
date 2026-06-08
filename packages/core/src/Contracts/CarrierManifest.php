<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;

interface CarrierManifest
{
    /**
     * Register a shipping carrier. Accepts a carrier instance, the class name
     * of a carrier, or a config array shape understood by the generic carrier.
     *
     * @param  ShippingCarrier|class-string<ShippingCarrier>|array<string, mixed>  $carrier
     * @return self
     */
    public function register(ShippingCarrier|string|array $carrier);

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
