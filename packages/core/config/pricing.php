<?php

use Lunar\Core\Pricing\DefaultPriceFormatter;

return [

    /*
    |--------------------------------------------------------------------------
    | Pricing Stored Inclusive of Tax
    |--------------------------------------------------------------------------
    |
    | Specify whether the prices entered into the system include tax or not.
    |
    */
    'stored_inclusive_of_tax' => env('LUNAR_STORE_INCLUSIVE_OF_TAX', false),

    /*
    |--------------------------------------------------------------------------
    | Price formatter
    |--------------------------------------------------------------------------
    |
    | Specify which class to use when formatting price data types
    |
    */
    'formatter' => DefaultPriceFormatter::class,

];
