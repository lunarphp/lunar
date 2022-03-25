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
        // These models are required by the system, do not change them.
        \GetCandy\Models\Collection::class,
        \GetCandy\Models\Product::class,
        \GetCandy\Models\ProductOption::class,
        \GetCandy\Models\Order::class,
        \GetCandy\Models\Customer::class,
        // Below you can add your own models for indexing
    ],
];
