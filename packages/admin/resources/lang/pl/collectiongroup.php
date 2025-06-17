<?php

return [

    'label' => 'Grupa kolekcji',

    'plural_label' => 'Grupy kolekcji',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'collections_count' => [
            'label' => 'Liczba kolekcji',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Tej grupy kolekcji nie można usunąć, ponieważ są z nią powiązane kolekcje.',
            ],
        ],
    ],
];
