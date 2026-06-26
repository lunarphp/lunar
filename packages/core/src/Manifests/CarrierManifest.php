<?php

namespace Lunar\Core\Manifests;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\CarrierManifest as CarrierManifestContract;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\Shipping\Carriers\Dpd;
use Lunar\Core\Shipping\Carriers\FedEx;
use Lunar\Core\Shipping\Carriers\RoyalMail;
use Lunar\Core\Shipping\Carriers\Ups;

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

        $this->registerCoreCarriers();
    }

    /**
     * {@inheritDoc}
     */
    public function register(ShippingCarrier|string $carrier)
    {
        $carrier = $this->resolve($carrier);

        $this->carriers->put($carrier->getKey(), $carrier);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function set(iterable $carriers)
    {
        $this->carriers = collect();

        foreach ($carriers as $carrier) {
            $this->register($carrier);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string ...$keys)
    {
        $this->carriers->forget($keys);

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
     * Register the batteries-included core carriers. A consumer adds or
     * overrides one by registering it from their own service provider.
     */
    protected function registerCoreCarriers(): void
    {
        $this->register(app(RoyalMail::class));
        $this->register(app(Dpd::class));
        $this->register(app(Ups::class));
        $this->register(app(FedEx::class));
    }

    /**
     * Normalise a registration into a carrier instance.
     *
     * @param  ShippingCarrier|class-string<ShippingCarrier>  $carrier
     */
    protected function resolve(ShippingCarrier|string $carrier): ShippingCarrier
    {
        if ($carrier instanceof ShippingCarrier) {
            return $carrier;
        }

        return app($carrier);
    }
}
