<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Base\Traits\HasModelExtending;

class ModelManifest implements ModelManifestInterface
{
    /**
     * The collection of models to register to this manifest.
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
     */
    public function getRegisteredModel(string $baseModelClass): Model
    {
        return app($this->models->get($baseModelClass) ?? $baseModelClass);
    }

    /**
     * Removes model from manifest.
     */
    public function removeModel(string $baseModelClass): void
    {
        $this->models = $this->models->flip()->forget($baseModelClass);
    }

    /**
     * Swap the model implementation.
     */
    public function swapModel(string $currentModelClass, string $newModelClass): void
    {
        $baseModelClass = $this->models->flip()->get($currentModelClass);

        $this->models->put($baseModelClass, $newModelClass);
    }

    /**
     * Get the morph class base model.
     */
    public function getMorphClassBaseModel(string $morphClass): ?string
    {
        $customModels = $this->models->flip();

        return $customModels->get($morphClass);
    }

    /**
     * Get list of registered base model classes.
     */
    public function getBaseModelClasses(): Collection
    {
        return $this->models->keys();
    }

    /**
     * Get list of all registered models.
     */
    public function getRegisteredModels(): Collection
    {
        return $this->models;
    }

    /**
     * Validate class is an eloquent model.
     *
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
