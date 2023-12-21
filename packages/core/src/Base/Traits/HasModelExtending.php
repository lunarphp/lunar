<?php

namespace Lunar\Base\Traits;

use Lunar\Facades\ModelManifest;

trait HasModelExtending
{
    /**
     * Returns the model class registered in the model manifest.
     */
    public static function modelClass(): string
    {
        $shortName = (new \ReflectionClass(static::class))->getShortName();

        $contractClass = 'Lunar\\Models\\Contracts\\'.$shortName;

        return ModelManifest::get($contractClass) ?? static::class;
    }
}
