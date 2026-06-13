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
    | Cancellation reasons
    |--------------------------------------------------------------------------
    |
    | The reasons an order can be cancelled, keyed by a stable identifier. The
    | label is shown in the cancel form and on the order timeline. The stored
    | key is what persists against the order.
    |
    */
    'cancel_reasons' => [
        'customer' => 'Customer changed/cancelled order',
        'items-unavailable' => 'Items unavailable',
        'fraud' => 'Fraudulent order',
        'declined' => 'Payment declined',
        'other' => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-close settled orders
    |--------------------------------------------------------------------------
    |
    | When enabled, an order is automatically closed (archived — dropping out
    | of the open work queue) the moment it becomes fully paid AND fully
    | fulfilled, reusing the CloseOrder action. It is close-only: a later
    | return or refund does not reopen it. Disabled by default, so closing
    | stays a deliberate action unless you opt in.
    |
    */
    'auto_close' => false,

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
    |   - derived payment_status (e.g. 'paid', 'refunded')
    |   - derived fulfilment_status (e.g. 'fulfilled')
    |   - per-parcel fulfilment state (e.g. 'shipped')
    |
    | Order-level notifications are sent by SendOrderPaymentStatusNotifications
    | and SendOrderFulfilmentStatusNotifications; per-parcel fulfilment
    | notifications by DefaultFulfilmentStateConfig::notificationsFor(). The
    | 'cancelled' key is sent by SendOrderCancelledNotifications when an order
    | is cancelled with "notify the customer" enabled.
    |
    */
    'notifications' => [
        // 'paid' => [
        //     App\Notifications\PaymentReceived::class,     // payment_status
        // ],
        // 'fulfilled' => [
        //     App\Notifications\OrderFulfilled::class,      // fulfilment_status
        // ],
        // 'cancelled' => [
        //     App\Notifications\OrderCancelled::class,      // order cancellation
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
