<?php

return [
    'label_plural' => 'Metody dostawy',
    'label' => 'Metoda dostawy',
    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'code' => [
            'label' => 'Kod',
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
            'label' => 'Opłata za',
            'options' => [
                'cart_total' => 'Całkowita wartość koszyka',
                'weight' => 'Waga',
            ],
        ],
        'driver' => [
            'label' => 'Typ',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Odbiór osobisty',
            ],
        ],
        'stock_available' => [
            'label' => 'Wszystkie produkty w koszyku muszą być dostępne',
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
            'label' => 'Nazwa',
        ],
        'code' => [
            'label' => 'Kod',
        ],
        'driver' => [
            'label' => 'Typ',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Odbiór osobisty',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Dostępność',
            'customer_groups' => 'Metoda dostawy jest obecnie niedostępna dla wszystkich grup klientów.',
        ],
    ],
];
