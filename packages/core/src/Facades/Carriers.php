<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\Manifests\CarrierManifest;

/**
 * @method static \Lunar\Core\Contracts\CarrierManifest register(ShippingCarrier|string $carrier)
 * @method static \Lunar\Core\Contracts\CarrierManifest set(iterable $carriers)
 * @method static \Lunar\Core\Contracts\CarrierManifest forget(string ...$keys)
 * @method static Collection all()
 * @method static ShippingCarrier|null get(?string $key)
 *
 * @see CarrierManifest
 */
class Carriers extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\CarrierManifest::class;
    }
}
