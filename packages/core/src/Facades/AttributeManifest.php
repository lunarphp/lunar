<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void addType(void $type, void $key = null)
 * @method static Collection getTypes()
 * @method static void getType(void $key)
 * @method static Collection getSearchableAttributes(string $attributeType)
 *
 * @see \Lunar\Core\Manifests\AttributeManifest
 */
class AttributeManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\AttributeManifest::class;
    }
}
