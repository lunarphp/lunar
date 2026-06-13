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

    /*
    |--------------------------------------------------------------------------
    | Fulfilment methods
    |--------------------------------------------------------------------------
    |
    | A fulfilment method owns a parcel's flow: its state graph, which order
    | lines it claims, and whether it carries carrier tracking. Core registers
    | three (shipping, collection, digital) built on the same seam; declare
    | extra data-shaped methods here (key, label, states, transitions,
    | default/fulfilled state, priority, tracking) and they are registered via
    | the GenericFulfilmentMethod. A method that needs real behaviour — line
    | claiming, custom logic — implements Lunar\Core\Contracts\FulfilmentMethod
    | and registers with the FulfilmentMethodManifest from a service provider
    | instead (container-for-behaviour, config-for-data).
    |
    | Example:
    |
    | 'slot-booking' => [
    |     'label' => 'Booked delivery',
    |     'states' => [Pending::class, Booked::class, Delivered::class, Cancelled::class],
    |     'transitions' => [
    |         Pending::class => [Booked::class, Cancelled::class],
    |         Booked::class => [Delivered::class, Pending::class, Cancelled::class],
    |         Delivered::class => [Booked::class],
    |         Cancelled::class => [],
    |     ],
    |     'default_state' => Pending::class,
    |     'fulfilled_state' => Delivered::class,
    |     'priority' => 40,
    |     'uses_tracking' => false,
    | ],
    |
    */
    'methods' => [
        //
    ],

];
