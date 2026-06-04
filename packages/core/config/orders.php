<?php

use Lunar\Core\Orders\ReferenceGenerator;
use Lunar\Core\Pipelines\Order\Creation\CleanUpOrderLines;
use Lunar\Core\Pipelines\Order\Creation\CreateOrderAddresses;
use Lunar\Core\Pipelines\Order\Creation\CreateOrderLines;
use Lunar\Core\Pipelines\Order\Creation\CreateShippingLine;
use Lunar\Core\Pipelines\Order\Creation\FillOrderFromCart;
use Lunar\Core\Pipelines\Order\Creation\MapDiscountBreakdown;

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

    /*
    |--------------------------------------------------------------------------
    | Order Status Notifications
    |--------------------------------------------------------------------------
    |
    | Notifications to dispatch when a state machine transitions into a given
    | state. Keyed by the state $name, each entry is a list of notification
    | class names. Each notification is instantiated with the model and
    | delivered via `notify()`.
    |
    | The same flat-key lookup covers every machine, keyed by $name:
    |   - headline order status (e.g. 'shipped', 'complete')
    |   - derived payment_status (e.g. 'paid', 'refunded')
    |   - derived fulfilment_status (e.g. 'fulfilled')
    |   - per-parcel fulfilment state (e.g. 'shipped')
    |
    | Read by DefaultOrderStateConfig::notificationsFor() and
    | DefaultFulfilmentStateConfig::notificationsFor(); swap those bindings
    | to source notifications from anywhere else.
    |
    */
    'notifications' => [
        // 'shipped' => [
        //     App\Notifications\OrderShipped::class,       // order status (0021)
        // ],
        // 'paid' => [
        //     App\Notifications\PaymentReceived::class,     // payment_status
        // ],
        // 'fulfilled' => [
        //     App\Notifications\OrderFulfilled::class,      // fulfilment_status
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Pipelines
    |--------------------------------------------------------------------------
    |
    | Define which pipelines should be run throughout an order's lifecycle.
    | The default ones provided should suit most needs, however you are
    | free to add your own as you see fit.
    |
    | Each pipeline class will be run from top to bottom.
    |
    */
    'pipelines' => [
        'creation' => [
            FillOrderFromCart::class,
            CreateOrderLines::class,
            CreateOrderAddresses::class,
            CreateShippingLine::class,
            CleanUpOrderLines::class,
            MapDiscountBreakdown::class,
        ],
    ],

];
