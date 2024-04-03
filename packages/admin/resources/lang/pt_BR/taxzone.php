<?php

return [

    'label' => 'Zona de Imposto',

    'plural_label' => 'Zonas de Imposto',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'zone_type' => [
            'label' => 'Tipo de Zona',
        ],
        'active' => [
            'label' => 'Ativo',
        ],
        'default' => [
            'label' => 'Padrão',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'zone_type' => [
            'label' => 'Tipo de Zona',
            'options' => [
                'country' => 'Limitar a Países',
                'states' => 'Limitar a Estados',
                'postcodes' => 'Limitar a Códigos Postais',
            ],
        ],
        'price_display' => [
            'label' => 'Exibição de Preço',
            'options' => [
                'include_tax' => 'Incluir Imposto',
                'exclude_tax' => 'Excluir Imposto',
            ],
        ],
        'active' => [
            'label' => 'Ativo',
        ],
        'default' => [
            'label' => 'Padrão',
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
            'label' => 'Códigos Postais',
            'helper' => 'Liste cada código postal em uma nova linha. Suporta caracteres coringa como NW*',
        ],

    ],

];
