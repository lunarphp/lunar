<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lunar\Base\BaseModel;
use Lunar\Facades\ModelManifest;
use ReflectionClass;

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

        if ($parentClass === BaseModel::class) {
            return parent::getTable();
        }

        if (! empty($this->table)) {
            return $this->table;
        }

        $rootModelClass = $this->resolveRootModelClass($parentClass);

        return $this->resolveBaseTableName($rootModelClass);
    }

    protected function resolveRootModelClass(string $childClass): string
    {
        $parentClass = get_parent_class($childClass);

        while ($parentClass && $parentClass !== BaseModel::class) {
            $childClass = $parentClass;
            $parentClass = get_parent_class($parentClass);
        }

        return $childClass;
    }

    protected function resolveBaseTableName(string $modelClass): string
    {
        $reflection = new ReflectionClass($modelClass);
        $defaultProperties = $reflection->getDefaultProperties();

        if (! empty($defaultProperties['table'])) {
            return $defaultProperties['table'];
        }

        /** @var Model $modelInstance */
        $modelInstance = $reflection->newInstanceWithoutConstructor();

        return $modelInstance->getTable();
    }

    public static function __callStatic($method, $parameters)
    {
        if (
            ! static::isLunarInstance()
        ) {
            return (new (static::modelClass()))->$method(...$parameters);
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
    public static function morphName(): string
    {
        return (new (static::modelClass()))->getMorphClass();
    }

    public function getMorphClass(): string
    {
        $morphMap = Relation::morphMap();

        foreach ([static::modelClass(), ...class_parents(static::class)] as $modelClass) {
            if ($morphClass = array_search($modelClass, $morphMap, true)) {
                return $morphClass;
            }
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
            $instance = new (static::modelClass());
        }

        foreach (Arr::wrap($classes) as $class) {
            if (static::observerIsAlreadyRegistered($instance, $class)) {
                continue;
            }

            $instance->registerObserver($class);
        }
    }

    /**
     * Whether the given observer already listens to every event it would be registered for.
     *
     * Model events are dispatched twice for a replaced model: under its own class name, and
     * under the Lunar model it replaces (see fireModelEvent()). Both classes boot their traits,
     * so an observer registered from a trait boot - Scout's ModelObserver, for one - would be
     * attached to both names and run twice for a single save.
     */
    protected static function observerIsAlreadyRegistered(Model $instance, object|string $class): bool
    {
        if (! static::$dispatcher instanceof Dispatcher) {
            return false;
        }

        $observer = is_object($class) ? $class::class : $class;

        $events = array_filter(
            $instance->getObservableEvents(),
            fn (string $event) => method_exists($class, $event)
        );

        if ($events === []) {
            return false;
        }

        $modelClasses = array_unique([
            $instance::class,
            static::lunarModelClass($instance::class),
        ]);

        $listeners = static::$dispatcher->getRawListeners();

        foreach ($events as $event) {
            $registered = array_merge(...array_map(
                fn (string $modelClass) => (array) ($listeners["eloquent.{$event}: {$modelClass}"] ?? []),
                $modelClasses
            ));

            if (! in_array("{$observer}@{$event}", $registered, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The Lunar model a replacement re-dispatches its events under.
     */
    protected static function lunarModelClass(string $modelClass): string
    {
        return str_replace('Contracts\\', '', ModelManifest::guessContractClass($modelClass));
    }

    /**
     * Fire the given event for the model.
     */
    protected function fireModelEvent($event, $halt = true): mixed
    {
        // Fire the actual models events
        $result = parent::fireModelEvent($event, $halt);

        $lunarClass = static::lunarModelClass(static::class);

        if ($lunarClass == static::class) {
            return $result;
        }

        return static::$dispatcher->{($halt ? 'until' : 'dispatch')}(
            "eloquent.{$event}: ".$lunarClass, $this
        );
    }
}
