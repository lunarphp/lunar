<?php

return [

    'label' => 'Grupa klientów',

    'plural_label' => 'Grupy klientów',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'default' => [
            'label' => 'Domyślna',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'default' => [
            'label' => 'Domyślna',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Tej grupy klientów nie można usunąć, ponieważ są z nią powiązani klienci.',
            ],
        ],
    ],
];
