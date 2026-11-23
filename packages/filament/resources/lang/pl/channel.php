<?php

return [
    'label' => 'Kanał',
    'plural_label' => 'Kanały',
    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Domyślny',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Domyślny',
        ],
    ],

    'actions' => [
        'delete' => [
            'blocked' => 'Nie można usunąć domyślnego kanału, kanałów z historią zamówień ani kanałów, które są nadal używane przez region lub koszyk. Zamiast tego oznacz kanał jako nieaktywny.',
        ],
    ],

];
