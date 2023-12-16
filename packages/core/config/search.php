<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models for indexing
    |--------------------------------------------------------------------------
    |
    | The model listed here will be used to create/populate the indexes.
    | You can provide your own model here to run them all on the same
    | search engine.
    |
    */
    'models' => [
        /*
         * These models are required by the system, do not change them.
         */
        Lunar\Models\Brand::class,
        Lunar\Models\Collection::class,
        Lunar\Models\Customer::class,
        Lunar\Models\Order::class,
        Lunar\Models\Product::class,
        Lunar\Models\ProductOption::class,

        /*
         * Below you can add your own models for indexing...
         */
        // App\Models\Example::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search engine mapping
    |--------------------------------------------------------------------------
    |
    | You can define what search driver each searchable model should use.
    | If the model isn't defined here, it will use the SCOUT_DRIVER env variable.
    |
    */
    'engine_map' => [
        // Lunar\Models\Product::class => 'algolia',
        // Lunar\Models\Order::class => 'meilisearch',
        // Lunar\Models\Collection::class => 'meilisearch',
    ],

    'indexers' => [
        Lunar\Models\Brand::class => Lunar\Search\BrandIndexer::class,
        Lunar\Models\Collection::class => Lunar\Search\CollectionIndexer::class,
        Lunar\Models\Customer::class => Lunar\Search\CustomerIndexer::class,
        Lunar\Models\Order::class => Lunar\Search\OrderIndexer::class,
        Lunar\Models\Product::class => Lunar\Search\ProductIndexer::class,
        Lunar\Models\ProductOption::class => Lunar\Search\ProductOptionIndexer::class,
    ],

];
