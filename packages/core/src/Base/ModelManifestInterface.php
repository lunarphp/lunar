<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ModelManifestInterface
{
    /**
     * Register models.
     */
    public function register(Collection $models): void;

    /**
     * Get the registered model for a base model class.
     */
    public function getRegisteredModel(string $baseModelClass): Model;

    /**
     * Get the morph class base model.
     */
    public function getMorphClassBaseModel(string $morphClass): ?string;

    /**
     * Get list of registered base model classes.
     */
    public function getBaseModelClasses(): Collection;
}
