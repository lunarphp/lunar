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
