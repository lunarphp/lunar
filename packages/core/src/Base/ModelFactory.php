<?php

namespace GetCandy\Base;

use GetCandy\Base\Traits\InteractsWithEloquentModel;
use Illuminate\Database\Eloquent\Model;

class ModelFactory
{
    /**
     * @var ModelFactory
     */
    protected static $instance;

    /**
     * @var array
     */
    protected static array $models = [];

    /**
     * Get the instance of the model factory.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Register models.
     *
     * @param  array  $models
     * @return void
     */
    public static function register(array $models): void
    {
        foreach ($models as $baseModelClass => $modelClass) {
            static::validateInteractsWithEloquent($baseModelClass);
            static::$models[$baseModelClass] = $modelClass;
        }
    }

    /**
     * Get the registered model for a base model class.
     *
     * @param  string  $baseModelClass
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getRegisteredModel(string $baseModelClass): Model
    {
        return app(static::$models[$baseModelClass] ?? $baseModelClass);
    }

    /**
     * Get list of registered base model classes.
     *
     * @return array
     */
    public static function getBaseModelClasses(): array
    {
        if (! static::$models) {
            return [];
        }

        return array_keys(static::$models);
    }

    /**
     * Validate base class interacts with eloquent model trait.
     *
     * @param  string  $baseClass
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private static function validateInteractsWithEloquent(string $baseClass): void
    {
        $uses = class_uses_recursive($baseClass);
        if (! isset($uses[InteractsWithEloquentModel::class])) {
            throw new \InvalidArgumentException(sprintf("Given [%s] doesn't use [%s] trait.", $baseClass, InteractsWithEloquentModel::class));
        }
    }
}
