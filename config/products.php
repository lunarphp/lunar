<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product identifiers
    |--------------------------------------------------------------------------
    |
    | Here you can specify certain validation rules and how they affect the way
    | product variants are stored in the database. By defauly everything is false
    | but you can set these values to true if you would like to enforce uniqueness
    | and make sure a value is specified in the hub.
    |
    */
    'sku' => [
        'required' => true,
        'unique'   => true,
    ],
    'gtin' => [
        'required' => false,
        'unique'   => false,
    ],
    'mpn' => [
        'required' => false,
        'unique'   => false,
    ],
    'ean' => [
        'required' => false,
        'unique'   => false,
    ],
];
