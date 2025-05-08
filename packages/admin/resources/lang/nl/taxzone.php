<?php

return [

    'label' => 'Belastingzone',

    'plural_label' => 'Belastingzones',

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'zone_type' => [
            'label' => 'Zonetype',
        ],
        'active' => [
            'label' => 'Actief',
        ],
        'default' => [
            'label' => 'Standaard',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'zone_type' => [
            'label' => 'Zonetype',
            'options' => [
                'country' => 'Beperk tot Landen',
                'states' => 'Beperk tot Staten',
                'postcodes' => 'Beperk tot Postcodes',
            ],
        ],
        'price_display' => [
            'label' => 'Prijsweergave',
            'options' => [
                'include_tax' => 'Inclusief Belasting',
                'exclude_tax' => 'Exclusief Belasting',
            ],
        ],
        'active' => [
            'label' => 'Actief',
        ],
        'default' => [
            'label' => 'Standaard',
        ],

        'zone_countries' => [
            'label' => 'Landen',
        ],

        'zone_country' => [
            'label' => 'Land',
        ],

        'zone_states' => [
            'label' => 'Staten',
        ],

        'zone_postcodes' => [
            'label' => 'Postcodes',
            'helper' => 'Plaats elke postcode op een nieuwe regel. Ondersteunt wildcards zoals NW*',
        ],

    ],

];
