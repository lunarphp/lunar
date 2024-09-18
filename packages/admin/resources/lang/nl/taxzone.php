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

        'zone_countries' => [
            'label' => 'Countries',
        ],

        'zone_country' => [
            'label' => 'Country',
        ],

        'zone_states' => [
            'label' => 'States',
        ],

        'zone_postcodes' => [
            'label' => 'Postcodes',
            'helper' => 'List each postcode on a new line. Supports wildcards such as NW*',
        ],

    ],

];
