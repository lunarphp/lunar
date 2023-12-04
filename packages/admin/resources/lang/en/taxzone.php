<?php

return [

    'label' => 'Tax Zone',

    'plural_label' => 'Tax Zones',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'zone_type' => [
            'label' => 'Zone Type',
        ],
        'active' => [
            'label' => 'Active',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'zone_type' => [
            'label' => 'Zone Type',
            'options' => [
                'country' => 'Limit to Countries',
                'states' => 'Limit to States',
                'postcodes' => 'Limit to Postcodes',
            ],
        ],
        'price_display' => [
            'label' => 'Price Display',
            'options' => [
                'include_tax' => 'Include Tax',
                'exclude_tax' => 'Exclude Tax',
            ],
        ],
        'active' => [
            'label' => 'Active',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],

];
