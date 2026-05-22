<?php

return [
    'label_plural' => 'Metode de livrare',
    'label' => 'Metodă de livrare',
    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'description' => [
            'label' => 'Descriere',
        ],
        'code' => [
            'label' => 'Cod',
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
            'label' => 'Taxare după',
            'options' => [
                'cart_total' => 'Total coș',
                'weight' => 'Greutate',
            ],
        ],
        'driver' => [
            'label' => 'Tip',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Ridicare',
            ],
        ],
        'stock_available' => [
            'label' => 'Stocul tuturor articolelor din coș trebuie să fie disponibil',
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
            'label' => 'Nume',
        ],
        'code' => [
            'label' => 'Cod',
        ],
        'driver' => [
            'label' => 'Tip',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Ridicare',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilitate',
            'customer_groups' => 'Această metodă de livrare este momentan indisponibilă pentru toate grupurile de clienți.',
        ],
    ],
];
