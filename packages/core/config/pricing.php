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

    /*
    |--------------------------------------------------------------------------
    | Automatic price conversion
    |--------------------------------------------------------------------------
    |
    | Automatic price conversion, recalculates amd updates non-default currency
    | prices of a purchasable, by the default currency exchange rate.
    |
    */
    'auto_conversion' => [
        /*
         |--------------------------------------------------------------------------
         | Enable automatic price conversion. (default is false)
         |--------------------------------------------------------------------------
         */
        'enabled' => false,
        /*
         |--------------------------------------------------------------------------
         | Specify on which queue connection the "job" should be pushed
         |--------------------------------------------------------------------------
         */
        'connection' => env('QUEUE_CONNECTION', 'sync'),
        /*
         |--------------------------------------------------------------------------
         | Specify on which queue name the "job" should be pushed
         |--------------------------------------------------------------------------
         */
        'queue' => 'default',
        /*
         |--------------------------------------------------------------------------
         | Chunk size of price records mass update
         |--------------------------------------------------------------------------
         */
        'update_chunk_size' => 100,
        /*
         |--------------------------------------------------------------------------
         | This job class is responsible for dispatching price conversion when
         | a non-default currency exchange rate changes
         |--------------------------------------------------------------------------
         */
        'currency_update_job' => \Lunar\Jobs\Prices\DispatchPriceConversionOnCurrencyUpdate::class,
        /*
         |--------------------------------------------------------------------------
         | This job class is responsible for dispatching price conversion when
         | a purchasable price (in default currency) changes
         |--------------------------------------------------------------------------
         */
        'price_update_job' => \Lunar\Jobs\Prices\DispatchPriceConversionOnPriceUpdate::class,
    ],

];
