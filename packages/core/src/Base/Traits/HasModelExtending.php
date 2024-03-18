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
        $contractClass = ModelManifest::guessContractClass(static::class);

        return ModelManifest::get($contractClass) ?? static::class;
    }

    public function getTable(): string
    {
        return $this->table ?? ModelManifest::getTable($this);
    }
}
