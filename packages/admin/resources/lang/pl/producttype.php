<?php

return [

    'label' => 'Typ produktu',

    'plural_label' => 'Typy produktów',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'products_count' => [
            'label' => 'Liczba produktów',
        ],
        'product_attributes_count' => [
            'label' => 'Atrybuty produktu',
        ],
        'variant_attributes_count' => [
            'label' => 'Atrybuty wariantu',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Atrybuty produktu',
        ],
        'variant_attributes' => [
            'label' => 'Atrybuty wariantu',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Brak grup atrybutów.',
        'no_attributes' => 'Brak atrybutów.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Tego typu produktu nie można usunąć, ponieważ są z nim powiązane produkty.',
            ],
        ],
    ],

];
