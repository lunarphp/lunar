<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Base\FieldTypeManifestInterface;

/**
 * @method static void add(string $classname)
 * @method static \Illuminate\Support\Collection getTypes()
 *
 * @see \Lunar\Core\Base\FieldTypeManifest
 */
class FieldTypeManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return FieldTypeManifestInterface::class;
    }
}
