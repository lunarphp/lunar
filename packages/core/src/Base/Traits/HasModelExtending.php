<?php

namespace GetCandy\Base\Traits;

use GetCandy\Facades\ModelManifest;
use Illuminate\Database\Eloquent\Model;
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
     * @param  string|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function swap(string $model = null): Model
    {
        if (! $model) {
            return ModelManifest::getRegisteredModel(get_called_class());
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = resolve($model);

        return $model->newInstance($this->attributesToArray(), $this->exists);
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
}
