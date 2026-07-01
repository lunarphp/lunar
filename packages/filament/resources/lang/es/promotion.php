<?php

return [

    'label' => 'Promoción',

    'plural_label' => 'Promociones',

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'description' => [
            'label' => 'Descripción',
        ],
        'starts_at' => [
            'label' => 'Fecha de Inicio',
        ],
        'ends_at' => [
            'label' => 'Fecha de Fin',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'discounts_count' => [
            'label' => 'N.º de descuentos',
        ],
        'starts_at' => [
            'label' => 'Fecha de Inicio',
        ],
        'ends_at' => [
            'label' => 'Fecha de Fin',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Descuentos',
            'description' => 'Los descuentos que pertenecen a esta campaña.',
            'actions' => [
                'associate' => [
                    'label' => 'Asociar un descuento',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'handle' => [
                    'label' => 'Identificador',
                ],
                'status' => [
                    'label' => 'Estado',
                ],
                'starts_at' => [
                    'label' => 'Fecha de Inicio',
                ],
                'ends_at' => [
                    'label' => 'Fecha de Fin',
                ],
            ],
        ],
    ],

];
