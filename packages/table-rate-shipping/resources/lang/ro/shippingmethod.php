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
        'cutoff' => [
            'label' => 'Termen limită',
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
                'flat-rate' => 'Tarif fix',
                'free-shipping' => 'Livrare gratuită',
            ],
        ],
        'stock_available' => [
            'label' => 'Stocul tuturor articolelor din coș trebuie să fie disponibil',
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
                'flat-rate' => 'Tarif fix',
                'free-shipping' => 'Livrare gratuită',
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
