<?php

return [

    'label' => 'Tip proizvoda',

    'plural_label' => 'Tipovi proizvoda',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'products_count' => [
            'label' => 'Broj proizvoda',
        ],
        'product_attributes_count' => [
            'label' => 'Atributi proizvoda',
        ],
        'variant_attributes_count' => [
            'label' => 'Atributi varijanti',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Atributi proizvoda',
        ],
        'variant_attributes' => [
            'label' => 'Atributi varijanti',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Nema dostupnih grupa atributa.',
        'no_attributes' => 'Nema dostupnih atributa.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ovaj tip proizvoda nije moguće izbrisati jer postoje povezani proizvodi.',
            ],
        ],
    ],

];
