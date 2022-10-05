<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ModelManifestInterface
{
    /**
     * Register models.
     *
     * @param  \Illuminate\Support\Collection  $models
     * @return void
     */
    public function register(Collection $models): void;

    /**
     * Get the registered model for a base model class.
     *
     * @param  string  $baseModelClass
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRegisteredModel(string $baseModelClass): Model;

    /**
     * Get the morph class base model.
     *
     * @param  string  $morphClass
     * @return string|null
     */
    public function getMorphClassBaseModel(string $morphClass): ?string;

    /**
     * Get list of registered base model classes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBaseModelClasses(): Collection;
}
