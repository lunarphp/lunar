<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\AttributeManifestInterface;

/**
 * @method static void addType(void $type, void $key = null)
 * @method static \Illuminate\Support\Collection getTypes()
 * @method static void getType(void $key)
 * @method static \Illuminate\Support\Collection getSearchableAttributes(string $attributeType)
 *
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
