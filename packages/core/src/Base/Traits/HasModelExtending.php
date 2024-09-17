<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lunar\Base\BaseModel;
use Lunar\Facades\ModelManifest;

trait HasModelExtending
{
    public function newModelQuery(): Builder
    {
        $concreteClass = static::modelClass();
        $parentClass = get_parent_class($concreteClass);

        // If they are both the same class i.e. they haven't changed
        // then just call the parent method.
        if ($parentClass == BaseModel::class || $this instanceof $concreteClass) {
            return parent::newModelQuery();
        }

        return $this->newEloquentBuilder(
            $this->newBaseQueryBuilder()
        )->setModel(
            static::withoutEvents(
                fn () => $this->replicateInto($concreteClass)
            )
        );
    }

    public function replicateInto($newClass)
    {
        $defaults = array_values(array_filter([
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
            ...$this->uniqueIds(),
            'laravel_through_key',
        ]));

        $attributes = Arr::except(
            $this->getAttributes(), $defaults
        );

        return tap(new $newClass, function ($instance) use ($attributes): Model {
            $instance->setRawAttributes($attributes);

            $instance->setRelations($this->relations);

            return $instance;
        });
    }

    public function getForeignKey(): string
    {
        $parentClass = get_parent_class($this);

        return $parentClass == BaseModel::class ? parent::getForeignKey() : Str::snake(class_basename($parentClass)).'_'.$this->getKeyName();

    }

    public function getTable()
    {
        $parentClass = get_parent_class($this);

        return $parentClass == BaseModel::class ? parent::getTable() : (new $parentClass)->table;
    }

    public static function __callStatic($method, $parameters)
    {
        if (
            ! static::isLunarInstance()
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

    /**
     * Returns the model alias registered in the model relation morph map.
     */
    public static function morphName():string{
        return (new (static::modelClass()))->getMorphClass();
    }
    
    public function getMorphClass(): string
    {
        $morphMap = Relation::morphMap();

        if ($customModelMorphMap = array_search(static::modelClass(), $morphMap, true)) {
            return $customModelMorphMap;
        }

        $parentClass = get_parent_class(static::class);

        if (ModelManifest::isLunarModel($parentClass) && $lunarModelMorphMap = array_search($parentClass, $morphMap, true)) {
            return $lunarModelMorphMap;
        }

        return parent::getMorphClass();
    }

    public static function isLunarInstance(): bool
    {
        return static::class == static::modelClass();
    }

    public static function observe($classes): void
    {
        $instance = new static;

        if (
            ! static::isLunarInstance()
        ) {
            $extendedClass = static::modelClass();
            $instance = new $extendedClass;
        }

        foreach (Arr::wrap($classes) as $class) {
            $instance->registerObserver($class);
        }
    }
}
