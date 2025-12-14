<?php

return [

    'label' => 'Grup de clienți',

    'plural_label' => 'Grupe de clienți',

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
    ],

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
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Acest grup de clienți nu poate fi șters deoarece are clienți asociați.',
            ],
        ],
    ],
];
