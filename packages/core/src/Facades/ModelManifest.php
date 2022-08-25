<?php

namespace GetCandy\Facades;

use GetCandy\Base\ModelManifestInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * Class ModelManifest.
 *
 * @method static \Illuminate\Support\Collection register(Collection $models)
 * @method static \Illuminate\Support\Collection getRegisteredModels()
 * @method static \Illuminate\Database\Eloquent\Model getRegisteredModel(string $baseModelClass)
 * @method static string getMorphClassBaseModel(string $morphClass)
 * @method static \Illuminate\Support\Collection getBaseModelClasses()
 *
 * @see \GetCandy\Base\ModelManifest
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
