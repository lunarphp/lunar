<?php

return [
    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'default' => [
            'label' => 'Implicit',
        ],
        'accepts_pickup' => [
            'label' => 'Clienții pot ridica de aici',
            'helper_text' => 'Oferă această locație ca punct de ridicare la finalizarea comenzii.',
        ],
        'address' => [
            'label' => 'Adresă',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'default' => [
            'label' => 'Implicit',
        ],
        'accepts_pickup' => [
            'label' => 'Ridicare',
        ],
        'fulfilments' => [
            'label' => 'Onorări',
        ],
    ],
];
