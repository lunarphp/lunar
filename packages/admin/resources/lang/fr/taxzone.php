<?php

return [

    'label' => 'Zone de taxe',

    'plural_label' => 'Zones de taxe',

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'zone_type' => [
            'label' => 'Type de zone',
        ],
        'active' => [
            'label' => 'Active',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'zone_type' => [
            'label' => 'Type de zone',
            'options' => [
                'country' => 'Limiter aux pays',
                'states' => 'Limiter aux départements',
                'postcodes' => 'Limiter aux codes postaux',
            ],
        ],
        'price_display' => [
            'label' => 'Affichage du prix',
            'options' => [
                'include_tax' => 'Inclure la taxe',
                'exclude_tax' => 'Exclure la taxe',
            ],
        ],
        'active' => [
            'label' => 'Active',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],

        'zone_countries' => [
            'label' => 'Pays',
        ],

        'zone_country' => [
            'label' => 'Pays',
        ],

        'zone_states' => [
            'label' => 'Départements',
        ],

        'zone_postcodes' => [
            'label' => 'Codes postaux',
            'helper' => 'Listez chaque code postal sur une nouvelle ligne. Prend en charge les jokers comme NW*',
        ],

    ],

];
