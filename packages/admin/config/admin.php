<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation counts
    |--------------------------------------------------------------------------
    |
    | The admin panel will show a count of orders in the left navigation.
    | This is based upon specific order status values. You can define the
    | statuses to include in the count below — see Lunar\Core\States\Order\Order
    | for the registered states (the static $name on each concrete class).
    |
    */
    'order_count_statuses' => ['in-process'],

];
