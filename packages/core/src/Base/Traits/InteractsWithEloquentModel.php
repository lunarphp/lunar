<?php

namespace GetCandy\Base\Traits;

use GetCandy\Base\ModelFactory;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithEloquentModel
{
    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     *
     * @return static|\Illuminate\Database\Eloquent\Model
     */
    public function newInstance($attributes = [], $exists = false): Model
    {
        $model = parent::newInstance($attributes, $exists);
        if (! in_array(get_called_class(), ModelFactory::getBaseModelClasses())) {
            return $model;
        }

        $model = ModelFactory::getInstance()->getRegisteredModel(get_class($model));
        return $model;
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
        $model = ModelFactory::getInstance()->getRegisteredModel(get_called_class());
        if (! in_array(get_called_class(), ModelFactory::getBaseModelClasses()) || ! $this->forwardCallsWhen($method, $model)) {
            return parent::__call($method, $parameters);
        }

        return $this->forwardCallTo($model, $method, $parameters);
    }

    /**
     * Swap the model implementation.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function swap(Model $model = null): Model
    {
        return $model ?? ModelFactory::getInstance()->getRegisteredModel(get_called_class());
    }

    /**
     * Swap the model implementation via static method.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function swapStatic(Model $model = null): Model
    {
        return $model ?? ModelFactory::getInstance()->getRegisteredModel(get_called_class());
    }

    /**
     * Forward a method call to the model only when calling a method on the model.
     *
     * @param  string  $method
     * @return bool
     */
    protected function forwardCallsWhen(string $method, Model $model): bool
    {
        $reflect = new \ReflectionClass($model);
        $methods = collect($reflect->getMethods())
            ->filter(fn ($method) => $method->class == get_class($model))
            ->map(fn ($method) => $method->name);

        return $methods->contains($method);
    }
}
