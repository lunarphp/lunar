<?php

return [

    'coupons' => [

        /*
        |--------------------------------------------------------------------------
        | Queue threshold
        |--------------------------------------------------------------------------
        |
        | The panel generates a batch of codes up to this size during the
        | request, and queues GenerateCouponsJob for anything larger.
        |
        */
        'queue_threshold' => 5000,

    ],

];
