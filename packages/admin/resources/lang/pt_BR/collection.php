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
            'label' => 'Coleções Filhas',
            'actions' => [
                'create_child' => [
                    'label' => 'Criar Coleção Filha',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Nº de Filhos',
                ],
                'name' => [
                    'label' => 'Nome',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Informações Básicas',
        ],
        'media' => [
            'label' => 'Mídia',
        ],
        'products' => [
            'label' => 'Produtos',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar Produto',
                ],
            ],
        ],
    ],

];
