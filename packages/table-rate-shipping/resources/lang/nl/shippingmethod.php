<?php

return [
    'label_plural' => 'Verzendmethoden',
    'label' => 'Verzendmethode',
    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'description' => [
            'label' => 'Beschrijving',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'schedule' => [
            'label' => 'Beschikbaarheidsschema',
            'days' => [
                'monday' => 'Maandag',
                'tuesday' => 'Dinsdag',
                'wednesday' => 'Woensdag',
                'thursday' => 'Donderdag',
                'friday' => 'Vrijdag',
                'saturday' => 'Zaterdag',
                'sunday' => 'Zondag',
            ],
            'from' => [
                'label' => 'Van',
            ],
            'to' => [
                'label' => 'Tot',
                'validation' => [
                    'after' => 'De eindtijd moet na de begintijd liggen.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Berekenen op basis van',
            'options' => [
                'cart_total' => 'Winkelwagentotaal',
                'weight' => 'Gewicht',
            ],
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standaard',
                'collection' => 'Afhalen',
            ],
        ],
        'stock_available' => [
            'label' => 'De voorraad van alle artikelen in de winkelwagen moet beschikbaar zijn',
        ],
        'weight_unit' => [
            'label' => 'Gewichtseenheid',
            'placeholder' => 'Geen gewichtsbeperking',
        ],
        'min_weight' => [
            'label' => 'Minimumgewicht',
        ],
        'max_weight' => [
            'label' => 'Maximumgewicht',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standaard',
                'collection' => 'Afhalen',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Beschikbaarheid',
            'customer_groups' => 'Deze verzendmethode is momenteel voor geen enkele klantengroep beschikbaar.',
        ],
    ],
];
