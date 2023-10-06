<?php

use Lunar\Pricing\DefaultPriceFormatter;

return [

    /*
    |--------------------------------------------------------------------------
    | Pricing Stored Inclusive of Tax
    |--------------------------------------------------------------------------
    |
    | Specify whether the prices entered into the system include tax or not.
    |
    */
    'stored_inclusive_of_tax' => false,

    /*
    |--------------------------------------------------------------------------
    | Price formatter
    |--------------------------------------------------------------------------
    |
    | Specify which class to use when formatting price data types
    |
    */
    'formatter' => DefaultPriceFormatter::class,

    /*
    |--------------------------------------------------------------------------
    | Pricing Pipelines
    |--------------------------------------------------------------------------
    |
    | Define which pipelines should be run when retrieving purchasable price.
    |
    | Each pipeline class will be run from top to bottom.
    |
    */
    'pipelines' => [
        // App\Pipelines\Pricing\Example::class,
    ],

];
