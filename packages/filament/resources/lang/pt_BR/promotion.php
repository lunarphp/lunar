<?php

return [

    'label' => 'Promoção',

    'plural_label' => 'Promoções',

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'description' => [
            'label' => 'Descrição',
        ],
        'starts_at' => [
            'label' => 'Data de início',
        ],
        'ends_at' => [
            'label' => 'Data de término',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'discounts_count' => [
            'label' => 'Qtd. de descontos',
        ],
        'starts_at' => [
            'label' => 'Data de início',
        ],
        'ends_at' => [
            'label' => 'Data de término',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Descontos',
            'description' => 'Os descontos que pertencem a esta campanha.',
            'actions' => [
                'associate' => [
                    'label' => 'Associar um desconto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
                'handle' => [
                    'label' => 'Identificador',
                ],
                'status' => [
                    'label' => 'Status',
                ],
                'starts_at' => [
                    'label' => 'Data de início',
                ],
                'ends_at' => [
                    'label' => 'Data de término',
                ],
            ],
        ],
    ],

];
