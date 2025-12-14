<?php

return [

    'label' => 'Attribútumcsoport',

    'plural_label' => 'Attribútumcsoportok',

    'table' => [
        'attributable_type' => [
            'label' => 'Típus',
        ],
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'position' => [
            'label' => 'Pozíció',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Típus',
        ],
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'position' => [
            'label' => 'Pozíció',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ez az attribútumcsoport nem törölhető, mivel vannak hozzá kapcsolódó attribútumok.',
            ],
        ],
    ],
];
