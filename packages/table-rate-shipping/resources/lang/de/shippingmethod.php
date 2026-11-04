<?php

return [
    'label_plural' => 'Versandarten',
    'label' => 'Versandart',
    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Beschreibung',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'schedule' => [
            'label' => 'Verfügbarkeitszeitplan',
            'days' => [
                'monday' => 'Montag',
                'tuesday' => 'Dienstag',
                'wednesday' => 'Mittwoch',
                'thursday' => 'Donnerstag',
                'friday' => 'Freitag',
                'saturday' => 'Samstag',
                'sunday' => 'Sonntag',
            ],
            'from' => [
                'label' => 'Von',
            ],
            'to' => [
                'label' => 'Bis',
                'validation' => [
                    'after' => 'Die Bis-Zeit muss nach der Von-Zeit liegen.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Berechnung nach',
            'options' => [
                'cart_total' => 'Warenkorbsumme',
                'weight' => 'Gewicht',
            ],
        ],
        'driver' => [
            'label' => 'Typ',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Abholung',
            ],
        ],
        'stock_available' => [
            'label' => 'Der Bestand aller Warenkorbartikel muss verfügbar sein',
        ],
        'weight_unit' => [
            'label' => 'Gewichtseinheit',
            'placeholder' => 'Keine Gewichtsbeschränkung',
        ],
        'min_weight' => [
            'label' => 'Mindestgewicht',
        ],
        'max_weight' => [
            'label' => 'Höchstgewicht',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'driver' => [
            'label' => 'Typ',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Abholung',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Verfügbarkeit',
            'customer_groups' => 'Diese Versandart ist derzeit für alle Kundengruppen nicht verfügbar.',
        ],
    ],
];
