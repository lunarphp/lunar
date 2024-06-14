<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Lunar\Models\Bundle;

trait HasBundles
{
    /**
     * Get all the models bundles.
     */
    public function bundles(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Bundle::class,
            'bundleable',
            "{$prefix}bundleables",
        )->withTimestamps();
    }


    /**
     * Add a bundle to the model.
     */
    public function addToBundle($bundle)
    {
        $this->bundles()->attach($bundle);
    }
}
