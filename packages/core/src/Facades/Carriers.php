<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Manifests\CarrierManifest;

/**
 * @method static \Lunar\Core\Contracts\CarrierManifest register(\Lunar\Core\Contracts\ShippingCarrier|string $carrier)
 * @method static \Lunar\Core\Contracts\CarrierManifest set(iterable $carriers)
 * @method static \Lunar\Core\Contracts\CarrierManifest forget(string ...$keys)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Lunar\Core\Contracts\ShippingCarrier|null get(?string $key)
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
