<?php

return [

    'label' => 'Currency',

    'plural_label' => 'Currencies',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'exchange_rate' => [
            'label' => 'Exchange Rate',
        ],
        'decimal_places' => [
            'label' => 'Decimal Places',
        ],
        'enabled' => [
            'label' => 'Enabled',
        ],
        'sync_prices' => [
            'label' => 'Sync Prices',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'exchange_rate' => [
            'label' => 'Exchange Rate',
        ],
        'decimal_places' => [
            'label' => 'Decimal Places',
        ],
        'enabled' => [
            'label' => 'Enabled',
        ],
        'default' => [
            'label' => 'Default',
        ],
        'sync_prices' => [
            'label' => 'Sync Prices',
            'helper_text' => 'Keep prices in this currency in sync with the default currency.',
        ],
    ],

];
