<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\ModelManifestInterface;

/**
 * Class ModelManifest.
 *
 * @method static \Illuminate\Support\Collection register()
 * @method static \Illuminate\Support\Collection getRegisteredModels()
 * @method static \Illuminate\Database\Eloquent\Model getRegisteredModel(string $baseModelClass)
 * @method static void removeModel(string $baseModelClass)
 * @method static void swapModel(string $currentModelClass, string $newModelClass)
 * @method static string getMorphClassBaseModel(string $morphClass)
 * @method static \Illuminate\Support\Collection getBaseModelClasses()
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
