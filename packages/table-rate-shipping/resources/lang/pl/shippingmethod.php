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
        'cutoff' => [
            'label' => 'Czas realizacji',
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
