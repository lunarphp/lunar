<?php

return [

    'label' => 'Zona Fiscal',

    'plural_label' => 'Zonas Fiscales',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'zone_type' => [
            'label' => 'Tipo de Zona',
        ],
        'active' => [
            'label' => 'Activo',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'zone_type' => [
            'label' => 'Tipo de Zona',
            'options' => [
                'country' => 'Limitar a Países',
                'states' => 'Limitar a Estados',
                'postcodes' => 'Limitar a Códigos Postales',
            ],
        ],
        'price_display' => [
            'label' => 'Visualización de Precios',
            'options' => [
                'include_tax' => 'Incluir Impuesto',
                'exclude_tax' => 'Excluir Impuesto',
            ],
        ],
        'active' => [
            'label' => 'Activo',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],

        'zone_countries' => [
            'label' => 'Países',
        ],

        'zone_country' => [
            'label' => 'País',
        ],

        'zone_states' => [
            'label' => 'Estados',
        ],

        'zone_postcodes' => [
            'label' => 'Códigos Postales',
            'helper' => 'Enumera cada código postal en una nueva línea. Soporta comodines como NW*',
        ],

    ],

];
