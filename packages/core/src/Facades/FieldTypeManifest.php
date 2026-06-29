<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Manifests\FieldTypeManifest as FieldTypeManifestImpl;

/**
 * @method static void add(string $type, string $class)
 * @method static void remove(string $type)
 * @method static string|null getType(string $type)
 * @method static Collection getTypes()
 *
 * @see FieldTypeManifestImpl
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
