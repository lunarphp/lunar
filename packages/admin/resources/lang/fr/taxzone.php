<?php

return [
    'label' => 'Zone fiscale',
    'plural_label' => 'Zones fiscales',
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'zone_type' => [
            'label' => 'Type de zone',
        ],
        'active' => [
            'label' => 'Actif',
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
                'pays' => 'Limiter aux pays',
                'etats' => 'Limiter aux états',
                'codes_postaux' => 'Limiter aux codes postaux',
            ],
        ],
        'price_display' => [
            'label' => 'Affichage des prix',
            'options' => [
                'avec_taxe' => 'Avec la TVA',
                'sans_taxe' => 'Sans la TVA',
            ],
        ],
        'active' => [
            'label' => 'Actif',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
        'zone_pays' => [
            'label' => 'Pays',
        ],
        'zone_etats' => [
            'label' => 'États',
        ],
        'zone_codes_postaux' => [
            'label' => 'Codes postaux',
            'helper' => 'Listez chaque code postal à une ligne. Prise en charge des wildcards telles que NW*',
        ],
    ],
];
