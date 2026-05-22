<?php

return [
    'label_plural' => 'Méthodes d\'expédition',
    'label' => 'Méthode d\'expédition',
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'schedule' => [
            'label' => 'Availability Schedule',
            'days' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'from' => [
                'label' => 'From',
            ],
            'to' => [
                'label' => 'Until',
                'validation' => [
                    'after' => 'The until time must be after the from time.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Facturer par',
            'options' => [
                'cart_total' => 'Total du panier',
                'weight' => 'Poids',
            ],
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Collecte',
            ],
        ],
        'stock_available' => [
            'label' => 'Le stock de tous les articles du panier doit être disponible',
        ],
        'weight_unit' => [
            'label' => 'Weight Unit',
            'placeholder' => 'No weight restriction',
        ],
        'min_weight' => [
            'label' => 'Minimum Weight',
        ],
        'max_weight' => [
            'label' => 'Maximum Weight',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Collecte',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilité',
            'customer_groups' => 'Cette méthode d\'expédition est actuellement indisponible pour tous les groupes de clients.',
        ],
    ],
];
