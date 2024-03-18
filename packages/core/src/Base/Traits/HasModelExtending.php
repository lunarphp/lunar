<?php

namespace Lunar\Base\Traits;

use Lunar\Facades\ModelManifest;

trait HasModelExtending
{
    public function newModelQuery()
    {
        $realClass = static::modelClass();

        return $this->newEloquentBuilder(
            $this->newBaseQueryBuilder()
        )->setModel(new $realClass);
    }

    /**
     * Returns the model class registered in the model manifest.
     */
    public static function modelClass(): string
    {
        $shortName = (new \ReflectionClass(static::class))->getShortName();

        $contractClass = 'Lunar\\Models\\Contracts\\'.$shortName;

        return ModelManifest::get($contractClass) ?? static::class;
    }

    public function getTable(): string
    {
        return $this->table ?? ModelManifest::getTable($this);
    }
}
