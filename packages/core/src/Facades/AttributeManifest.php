<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\AttributeManifestInterface;

/**
 * @see \Lunar\Base\AttributeManifest
 */
class AttributeManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return AttributeManifestInterface::class;
    }
}
