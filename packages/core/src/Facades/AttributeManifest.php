<?php

namespace GetCandy\Facades;

use GetCandy\Base\AttributeManifestInterface;
use Illuminate\Support\Facades\Facade;

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
