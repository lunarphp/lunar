<?php

return [

    'label' => 'Grupa kupaca',

    'plural_label' => 'Grupe kupaca',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ovu grupu kupaca nije moguće izbrisati jer postoje povezani kupci.',
            ],
        ],
    ],
];
