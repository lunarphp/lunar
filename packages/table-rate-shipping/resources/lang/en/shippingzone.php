<?php

return [
    'label' => 'Shipping Zone',
    'label_plural' => 'Shipping Zones',
    'form' => [
        'unrestricted' => [
            'content' => 'This shipping zone has no restrictions in place and will be available to all customers at checkout.',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'type' => [
            'label' => 'Type',
            'options' => [
                'unrestricted' => 'Unrestricted',
                'countries' => 'Limit to Countries',
                'states' => 'Limit to States / Provinces',
                'postcodes' => 'Limit to Postcodes',
            ],
        ],
        'country' => [
            'label' => 'Country',
        ],
        'states' => [
            'label' => 'States',
        ],
        'countries' => [
            'label' => 'States',
        ],
        'postcodes' => [
            'label' => 'Postcodes',
            'helper' => 'List each postcode on a new line. Supports wildcards such as NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'type' => [
            'label' => 'Type',
            'options' => [
                'unrestricted' => 'Unrestricted',
                'countries' => 'Limit to Countries',
                'states' => 'Limit to States / Provinces',
                'postcodes' => 'Limit to Postcodes',
            ],
        ],
    ],
];
