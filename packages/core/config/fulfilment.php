<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hold reasons
    |--------------------------------------------------------------------------
    |
    | The reasons a fulfilment can be placed on hold, keyed by a stable
    | identifier. The label is shown in the admin hold form and on the held
    | badge. Republish this config to add, remove, or relabel reasons; the
    | stored key is what persists against the fulfilment.
    |
    */
    'hold_reasons' => [
        'awaiting-payment' => 'Awaiting payment',
        'out-of-stock' => 'Inventory out of stock',
        'incorrect-address' => 'Incorrect address',
        'high-risk' => 'High risk of fraud',
        'other' => 'Other',
    ],

];
