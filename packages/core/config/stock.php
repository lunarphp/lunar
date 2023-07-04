<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stock Driver
    |--------------------------------------------------------------------------
    |
    | Here you can specify which stock driver should be used. By default "simple" is used
    | and should work for you in most cases.
    |
    */
    'driver' => 'simple',

    /*
    |--------------------------------------------------------------------------
    | Reservation Duration
    |--------------------------------------------------------------------------
    |
    | The amount of time, in minutes, stock should be reserved for.
    |
    */
    'reservation_duration' => 30,

    /*
    |--------------------------------------------------------------------------
    | Auto-Dispatch
    |--------------------------------------------------------------------------
    |
    | Setting this to "true" will dispatch order lines upon creation, so that
    | stock is automatically deducted straight away.
    |
    */
    'auto-dispatch' => false,
];
