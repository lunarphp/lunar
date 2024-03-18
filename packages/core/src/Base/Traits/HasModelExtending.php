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

    public static function __callStatic($method, $parameters)
    {
        if (
            static::modelClass() != static::class
        ) {
            $extendedClass = static::modelClass();

            return (new $extendedClass)->$method(...$parameters);
        }

        return (new static)->$method(...$parameters);
    }

    /**
     * Returns the model class registered in the model manifest.
     */
    public static function modelClass(): string
    {
        $contractClass = ModelManifest::guessContractClass(static::class);

        return ModelManifest::get($contractClass) ?? static::class;
    }

    public function getTable(): string
    {
        return $this->table ?? ModelManifest::getTable($this);
    }
}
