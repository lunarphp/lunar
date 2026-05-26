<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\FieldTypes\Manifest;

/**
 * @method static void add(string $classname)
 * @method static \Illuminate\Support\Collection getTypes()
 *
 * @see Manifest
 */
class FieldTypeManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\FieldTypeManifest::class;
    }
}
