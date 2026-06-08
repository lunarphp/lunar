<?php

namespace Lunar\Core\Manifests;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\CarrierManifest as CarrierManifestContract;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\Shipping\GenericCarrier;

class CarrierManifest implements CarrierManifestContract
{
    /**
     * The registered carriers, keyed by carrier key.
     *
     * @var Collection<string, ShippingCarrier>
     */
    protected Collection $carriers;

    public function __construct()
    {
        $this->carriers = collect();

        $this->registerConfiguredCarriers();
    }

    /**
     * {@inheritDoc}
     */
    public function register(ShippingCarrier|string|array $carrier)
    {
        $carrier = $this->resolve($carrier);

        $this->carriers->put($carrier->getKey(), $carrier);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->carriers;
    }

    /**
     * {@inheritDoc}
     */
    public function get(?string $key): ?ShippingCarrier
    {
        if ($key === null) {
            return null;
        }

        return $this->carriers->get($key);
    }

    /**
     * Register every carrier defined in the shipping config.
     */
    protected function registerConfiguredCarriers(): void
    {
        foreach (config('lunar.shipping.carriers', []) as $key => $config) {
            $this->register(GenericCarrier::fromConfig($key, $config));
        }
    }

    /**
     * Normalise the supported registration shapes into a carrier instance.
     *
     * @param  ShippingCarrier|class-string<ShippingCarrier>|array<string, mixed>  $carrier
     */
    protected function resolve(ShippingCarrier|string|array $carrier): ShippingCarrier
    {
        if ($carrier instanceof ShippingCarrier) {
            return $carrier;
        }

        if (is_array($carrier)) {
            $key = $carrier['key'] ?? throw new \InvalidArgumentException('A carrier config array must include a "key".');

            return GenericCarrier::fromConfig($key, $carrier);
        }

        return app($carrier);
    }
}
