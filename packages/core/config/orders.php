<?php

use Lunar\Core\Orders\ReferenceGenerator;

return [
    /*
    |--------------------------------------------------------------------------
    | Order Reference Format
    |--------------------------------------------------------------------------
    |
    | Specify the format for the order reference generator to use.
    |
    */
    'reference_format' => [
        /**
         * Optional prefix for the order reference
         */
        'prefix' => null,

        /**
         * STR_PAD_LEFT: 00001965
         * STR_PAD_RIGHT: 19650000
         * STR_PAD_BOTH: 00196500
         */
        'padding_direction' => STR_PAD_LEFT,

        /**
         * 00001965
         * AAAA1965
         */
        'padding_character' => '0',

        /**
         * If the length specified below is smaller than the length
         * of the Order ID, then no padding will take place.
         */
        'length' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Reference Generator
    |--------------------------------------------------------------------------
    |
    | Here you can specify how you want your order references to be generated
    | when you create an order from a cart.
    |
    */
    'reference_generator' => ReferenceGenerator::class,

];
