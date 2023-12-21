<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Lunar\Models\Contracts;
use Lunar\Models;

class ModelManifest implements ModelManifestInterface
{
    /**
     * The collection of models to register to this manifest.
     */
    protected array $models = [
        Contracts\ProductType::class => [
            'model' => Models\ProductType::class,
            'binding' => 'productType',
        ],
    ];

    /**
     * Bind initial models in container and set explicit model binding.
     */
    public function register(): void
    {
        foreach ($this->models as $interface => $data) {
            app()->bind($interface, $data['model']);
            Route::model($data['binding'], $data['model']);
        }
    }

    /**
     * Register models.
     */
    public function add(string $interfaceClass, string $modelClass, string $binding): void
    {
        $this->validateClassIsEloquentModel($modelClass);

        $this->models[$interfaceClass] = [
            'model' => $modelClass,
            'binding' => $binding,
        ];

        // Bind in container
        app()->bind($interfaceClass, $modelClass);

        // Route model binding
        Route::model($binding, $modelClass);
    }

    /**
     * Replace a model with a different implementation.
     */
    public function replace(string $interfaceClass, string $modelClass): void
    {
        $this->validateClassIsEloquentModel($modelClass);
        $this->models[$interfaceClass]['model'] = $modelClass;

        // Bind in container
        app()->bind($interfaceClass, $modelClass);

        // Route model binding
        Route::model($this->models[$interfaceClass]['binding'], $modelClass);
    }

    /**
     * Gets the registered class for the interface.
     */
    public function get(string $interfaceClass)
    {
        return $this->models[$interfaceClass];
    }

    /**
     * Validate class is an eloquent model.
     *
     * @throws \InvalidArgumentException
     */
    private function validateClassIsEloquentModel(string $class): void
    {
        if (! is_subclass_of($class, Model::class)) {
            throw new \InvalidArgumentException(sprintf('Given [%s] is not a subclass of [%s].', $class, Model::class));
        }
    }
}
