<?php

return [
    'enabled' => env('LUNAR_SHIPPING_TABLES_ENABLED', true),

    /*
     * What method should we use for a shipping rate tax calculation?
     * Options are 'default' for the system-wide default tax rate,
     * 'highest' to select the highest tax rate in the cart
     * or add a callable to use your own logic (eg => [MyTaxRateCalculator::class, 'calculate'])
     */
    'shipping_rate_tax_calculation' => 'default',

];
