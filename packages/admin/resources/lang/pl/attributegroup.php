<?php

return [

    'label' => 'Grupa atrybutów',

    'plural_label' => 'Grupy atrybutów',

    'table' => [
        'attributable_type' => [
            'label' => 'Typ',
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'position' => [
            'label' => 'Pozycja',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Typ',
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'position' => [
            'label' => 'Pozycja',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Tej grupy atrybutów nie można usunąć, ponieważ są z nią powiązane atrybuty.',
            ],
        ],
    ],
];
