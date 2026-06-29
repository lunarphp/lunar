<?php

return [

    'label' => 'Región',

    'plural_label' => 'Regiones',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'currency' => [
            'label' => 'Moneda',
        ],
        'language' => [
            'label' => 'Idioma',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'currency' => [
            'label' => 'Moneda',
        ],
        'language' => [
            'label' => 'Idioma',
        ],
        'tax_zone' => [
            'label' => 'Zona fiscal de visualización',
        ],
        'prices_inc_tax' => [
            'label' => 'Visualización de precios',
            'options' => [
                'inherit' => 'Usar valor predeterminado de la tienda',
                'inclusive' => 'Impuestos incluidos',
                'exclusive' => 'Impuestos excluidos',
            ],
        ],
        'countries' => [
            'label' => 'Países',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

];
