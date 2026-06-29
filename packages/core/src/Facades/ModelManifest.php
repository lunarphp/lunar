<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register()
 * @method static void addDirectory(string $dir)
 * @method static void morphMap()
 * @method static string getMorphMapKey(string $className)
 *
 * @see \Lunar\Core\Manifests\ModelManifest
 */
class ModelManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\ModelManifest::class;
    }
}
