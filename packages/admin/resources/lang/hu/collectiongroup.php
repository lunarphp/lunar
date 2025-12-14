<?php

return [

    'label' => 'Gyűjteménycsoport',

    'plural_label' => 'Gyűjteménycsoportok',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'collections_count' => [
            'label' => 'Gyűjtemények száma',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ez a gyűjteménycsoport nem törölhető, mert gyűjtemények kapcsolódnak hozzá.',
            ],
        ],
    ],
];
