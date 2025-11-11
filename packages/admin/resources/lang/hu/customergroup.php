<?php

return [

    'label' => 'Vásárlói csoport',

    'plural_label' => 'Vásárlói csoportok',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ez a vásárlói csoport nem törölhető, mert vásárlók kapcsolódnak hozzá.',
            ],
        ],
    ],
];
