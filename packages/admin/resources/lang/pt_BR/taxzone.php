<?php

return [

    'label' => 'Zona de imposto',

    'plural_label' => 'Zonas de imposto',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'zone_type' => [
            'label' => 'Tipo de zona',
        ],
        'active' => [
            'label' => 'Ativa',
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
            'label' => 'Tipo de zona',
            'options' => [
                'country' => 'Limitar a países',
                'states' => 'Limitar a estados',
                'postcodes' => 'Limitar a CEPs',
            ],
        ],
        'price_display' => [
            'label' => 'Exibição de preço',
            'options' => [
                'include_tax' => 'Incluir imposto',
                'exclude_tax' => 'Excluir imposto',
            ],
        ],
        'active' => [
            'label' => 'Ativa',
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
            'label' => 'CEPs',
            'helper' => 'Liste cada CEP em uma nova linha. Suporta curingas como NW*',
        ],

    ],

];
