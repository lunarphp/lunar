<?php

return [
    'label_plural' => 'Szállítási módok',
    'label' => 'Szállítási mód',
    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'description' => [
            'label' => 'Leírás',
        ],
        'code' => [
            'label' => 'Kód',
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
            'label' => 'Számlázás',
            'options' => [
                'cart_total' => 'Kosár végösszeg',
                'weight' => 'Súly',
            ],
        ],
        'driver' => [
            'label' => 'Típus',
            'options' => [
                'ship-by' => 'Házhozszállítás',
                'collection' => 'Személyes átvétel',
            ],
        ],
        'stock_available' => [
            'label' => 'A kosárban lévő összes terméknek raktáron kell lennie',
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
            'label' => 'Név',
        ],
        'code' => [
            'label' => 'Kód',
        ],
        'driver' => [
            'label' => 'Típus',
            'options' => [
                'ship-by' => 'Házhozszállítás',
                'collection' => 'Személyes átvétel',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Elérhetőség',
            'customer_groups' => 'Ez a szállítási mód jelenleg egyik vásárlói csoport számára sem elérhető.',
        ],
    ],
];
