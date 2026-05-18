<?php

use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Customer;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Search\BrandIndexer;
use Lunar\Search\CollectionIndexer;
use Lunar\Search\CustomerIndexer;
use Lunar\Search\OrderIndexer;
use Lunar\Search\ProductIndexer;
use Lunar\Search\ProductOptionIndexer;

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
        Brand::class,
        Collection::class,
        Customer::class,
        Order::class,
        Product::class,
        ProductOption::class,

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
        Brand::class => BrandIndexer::class,
        Collection::class => CollectionIndexer::class,
        Customer::class => CustomerIndexer::class,
        Order::class => OrderIndexer::class,
        Product::class => ProductIndexer::class,
        ProductOption::class => ProductOptionIndexer::class,
    ],

];
