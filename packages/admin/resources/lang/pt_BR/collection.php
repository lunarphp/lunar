<?php

return [

    'label' => 'Coleção',

    'plural_label' => 'Coleções',

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Coleções filhas',
            'actions' => [
                'create_child' => [
                    'label' => 'Criar coleção filha',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Qtd. de filhas',
                ],
                'name' => [
                    'label' => 'Nome',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Informações básicas',
        ],
        'products' => [
            'label' => 'Produtos',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar produto',
                ],
            ],
        ],
    ],

];
