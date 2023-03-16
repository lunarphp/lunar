<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Base\Traits\HasModelExtending;

class ModelManifest implements ModelManifestInterface
{
    /**
     * The collection of models to register to this manifest.
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $models;

    /**
     * The model manifest instance.
     */
    public function __construct()
    {
        $this->models = collect();
    }

    /**
     * Register models.
     *
     * @param  \Illuminate\Support\Collection  $models
     * @return void
     */
    public function register(Collection $models): void
    {
        foreach ($models as $baseModelClass => $modelClass) {
            $this->validateInteractsWithEloquent($baseModelClass);
            $this->validateClassIsEloquentModel($modelClass);

            $this->models->put($baseModelClass, $modelClass);
        }
    }

    /**
     * Get the registered model for a base model class.
     *
     * @param  string  $baseModelClass
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRegisteredModel(string $baseModelClass): Model
    {
        return app($this->models->get($baseModelClass) ?? $baseModelClass);
    }

    /**
     * Get the registered model class for a base model class.
     *
     * @param  string  $baseModelClass
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRegisteredModelClass(string $baseModelClass): string
    {
        return $this->models->get($baseModelClass) ?? $baseModelClass;
    }

    /**
     * Removes model from manifest.
     *
     * @param  string  $baseModelClass
     * @return void
     */
    public function removeModel(string $baseModelClass): void
    {
        $this->models = $this->models->flip()->forget($baseModelClass);
    }

    /**
     * Swap the model implementation.
     *
     * @param  string  $currentModelClass
     * @param  string  $newModelClass
     * @return void
     */
    public function swapModel(string $currentModelClass, string $newModelClass): void
    {
        $baseModelClass = $this->models->flip()->get($currentModelClass);

        $this->models->put($baseModelClass, $newModelClass);
    }

    /**
     * Get the morph class base model.
     *
     * @param  string  $morphClass
     * @return string|null
     */
    public function getMorphClassBaseModel(string $morphClass): ?string
    {
        $customModels = $this->models->flip();

        return $customModels->get($morphClass);
    }

    /**
     * Get list of registered base model classes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBaseModelClasses(): Collection
    {
        return $this->models->keys();
    }

    /**
     * Get list of all registered models.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRegisteredModels(): Collection
    {
        return $this->models;
    }

    /**
     * Validate class is an eloquent model.
     *
     * @param  string  $class
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateClassIsEloquentModel(string $class): void
    {
        if (! is_subclass_of($class, Model::class)) {
            throw new \InvalidArgumentException(sprintf('Given [%s] is not a subclass of [%s].', $class, Model::class));
        }
    }

    /**
     * Validate base class interacts with eloquent model trait.
     *
     * @param  string  $baseClass
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateInteractsWithEloquent(string $baseClass): void
    {
        $uses = class_uses_recursive($baseClass);
        if (! isset($uses[HasModelExtending::class])) {
            throw new \InvalidArgumentException(sprintf("Given [%s] doesn't use [%s] trait.", $baseClass, HasModelExtending::class));
        }
    }
}
