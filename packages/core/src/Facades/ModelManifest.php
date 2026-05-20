<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Base\ModelManifestInterface;

/**
 * @method static void register()
 * @method static void addDirectory(string $dir)
 * @method static void add(string $interfaceClass, string $modelClass)
 * @method static void replace(string $interfaceClass, string $modelClass)
 * @method static string|null get(string $interfaceClass)
 * @method static string guessContractClass(string $modelClass)
 * @method static string guessModelClass(string $modelContract)
 * @method static string|null findLunarModel(\Lunar\Core\Base\BaseModel|string $model)
 * @method static bool isLunarModel(\Lunar\Core\Base\BaseModel|string $model)
 * @method static void morphMap()
 * @method static string getMorphMapKey(void $className)
 *
 * @see \Lunar\Core\Base\ModelManifest
 */
class ModelManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ModelManifestInterface::class;
    }
}
