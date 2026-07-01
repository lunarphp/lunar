<?php

return [

    'label' => 'Promotion',

    'plural_label' => 'Promotions',

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'starts_at' => [
            'label' => 'Date de début',
        ],
        'ends_at' => [
            'label' => 'Date de fin',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'discounts_count' => [
            'label' => 'Nbre de réductions',
        ],
        'starts_at' => [
            'label' => 'Date de début',
        ],
        'ends_at' => [
            'label' => 'Date de fin',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Réductions',
            'description' => 'Les réductions appartenant à cette campagne.',
            'actions' => [
                'associate' => [
                    'label' => 'Associer une réduction',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'handle' => [
                    'label' => 'Identifiant',
                ],
                'status' => [
                    'label' => 'Statut',
                ],
                'starts_at' => [
                    'label' => 'Date de début',
                ],
                'ends_at' => [
                    'label' => 'Date de fin',
                ],
            ],
        ],
    ],

];
