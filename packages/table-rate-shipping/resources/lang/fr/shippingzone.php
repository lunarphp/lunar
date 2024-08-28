<?php

return [
    'label' => 'Zone d\'expédition',
    'label_plural' => 'Zones d\'expédition',
    'form' => [
        'unrestricted' => [
            'content' => 'Cette zone d\'expédition n\'a aucune restriction en place et sera disponible pour tous les clients lors du paiement.',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'type' => [
            'label' => 'Type',
            'options' => [
                'unrestricted' => 'Sans restriction',
                'countries' => 'Limiter aux pays',
                'states' => 'Limiter aux départements / régions',
                'postcodes' => 'Limiter aux codes postaux',
            ],
        ],
        'country' => [
            'label' => 'Pays',
        ],
        'states' => [
            'label' => 'Départements',
        ],
        'countries' => [
            'label' => 'Pays',
        ],
        'postcodes' => [
            'label' => 'Codes postaux',
            'helper' => 'Listez chaque code postal sur une nouvelle ligne. Prend en charge les jokers comme NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'type' => [
            'label' => 'Type',
            'options' => [
                'unrestricted' => 'Sans restriction',
                'countries' => 'Limiter aux pays',
                'states' => 'Limiter aux départements / régions',
                'postcodes' => 'Limiter aux codes postaux',
            ],
        ],
    ],
];
