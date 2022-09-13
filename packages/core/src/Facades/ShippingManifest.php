<?php

namespace Lunar\Facades;

use Lunar\Base\ShippingManifestInterface;
use Illuminate\Support\Facades\Facade;

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
