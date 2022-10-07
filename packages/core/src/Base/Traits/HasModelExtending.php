<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use Lunar\Facades\ModelManifest;
use ReflectionClass;

trait HasModelExtending
{
    /**
     * Get new instance of the registered model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static|\Illuminate\Database\Eloquent\Model
     */
    public function newInstance($attributes = [], $exists = false): Model
    {
        $model = parent::newInstance($attributes, $exists);
        if (! ModelManifest::getBaseModelClasses()->contains(get_called_class())) {
            return $model;
        }

        $model = ModelManifest::getRegisteredModel(get_class($model));

        return $model->newInstance($attributes, $exists);
    }

    /**
     * Handle dynamic and static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $model = ModelManifest::getRegisteredModel(get_called_class());

        if (! ModelManifest::getBaseModelClasses()->contains(get_called_class()) || ! $this->forwardCallsWhen($method, $model)) {
            return parent::__call($method, $parameters);
        }

        return $this->forwardCallTo($model, $method, $parameters);
    }

    /**
     * Swap the model implementation.
     *
     * @param  string  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function swap(string $newModelClass): Model
    {
        ModelManifest::swapModel(get_class($this), $newModelClass);

        /** @var Model $newModelClass */
        $newModelClass = resolve($newModelClass);

        return $newModelClass->newInstance($this->attributesToArray(), $this->exists);
    }

    /**
     * Get the class name of the base model.
     *
     * @return string
     */
    public function getMorphClass(): string
    {
        $morphClass = ModelManifest::getMorphClassBaseModel(get_class($this));

        return $this->morphClass ?: ($morphClass ?? parent::getMorphClass());
    }

    /**
     * Forward a method call to the model only when calling a method on the model.
     *
     * @param  string  $method
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function forwardCallsWhen(string $method, Model $model): bool
    {
        $reflect = new ReflectionClass($model);
        $methods = collect($reflect->getMethods())
            ->filter(fn ($method) => $method->class == get_class($model))
            ->map(fn ($method) => $method->name);

        return $methods->contains($method);
    }

    /**
     * Register a model event with the dispatcher.
     *
     * @param  string  $event
     * @param  \Illuminate\Events\QueuedClosure|\Closure|string  $callback
     * @return void
     */
    protected static function registerModelEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = ModelManifest::getMorphClassBaseModel(static::class) ?? static::class;

            static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback);
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  bool  $halt
     * @return mixed
     */
    protected function fireModelEvent($event, $halt = true)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event. If that
        // returns a result we can return that result, or we'll call the string events.
        $method = $halt ? 'until' : 'dispatch';

        $result = $this->filterModelEventResults(
            $this->fireCustomModelEvent($event, $method)
        );

        if ($result === false) {
            return false;
        }

        $name = ModelManifest::getMorphClassBaseModel(static::class) ?? static::class;

        return ! empty($result) ? $result : static::$dispatcher->{$method}(
            "eloquent.{$event}: {$name}", $this
        );
    }

    /**
     * Remove all the event listeners for the model.
     *
     * @return void
     */
    public static function flushEventListeners()
    {
        if (! isset(static::$dispatcher)) {
            return;
        }

        $instance = new static;

        $name = ModelManifest::getMorphClassBaseModel(static::class) ?? static::class;
        foreach ($instance->getObservableEvents() as $event) {
            static::$dispatcher->forget("eloquent.{$event}: {$name}");
        }

        foreach (array_values($instance->dispatchesEvents) as $event) {
            static::$dispatcher->forget($event);
        }
    }
}
