<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\ShippingManifestInterface;

/**
 * @see \Lunar\Base\ShippingManifest
 */
class ShippingManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ShippingManifestInterface::class;
    }
}
