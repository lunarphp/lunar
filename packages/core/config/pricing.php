<?php

return [
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
