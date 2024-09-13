<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\BaseModel;
use Lunar\Base\ModelManifestInterface;

/**
 * Class ModelManifest.
 *
 * @method static void register()
 * @method static void addDirectory(string $dir)
 * @method static void add(string $interfaceClass, string $modelClass)
 * @method static void replace(string $interfaceClass, string $modelClass)
 * @method static string|null get(string $interfaceClass)
 * @method static string guessContractClass(string $modelClass)
 * @method static string guessModelClass(string $modelContract)
 * @method static bool isLunarModel(string|BaseModel $model)
 * @method static string getTable(BaseModel $model)
 * @method static void morphMap()
 * @method static string getMorphMapKey(string $className)
 *
 * @see \Lunar\Base\ModelManifest
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
