<?php

use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Search\BrandIndexer;
use Lunar\Core\Search\CollectionIndexer;
use Lunar\Core\Search\CustomerIndexer;
use Lunar\Core\Search\OrderIndexer;
use Lunar\Core\Search\ProductIndexer;
use Lunar\Core\Search\ProductOptionIndexer;

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
        // Lunar\Core\Models\Product::class => 'algolia',
        // Lunar\Core\Models\Order::class => 'meilisearch',
        // Lunar\Core\Models\Collection::class => 'meilisearch',
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
