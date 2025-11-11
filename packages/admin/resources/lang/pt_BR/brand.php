<?php

return [

    'label' => 'Marca',

    'plural_label' => 'Marcas',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'products_count' => [
            'label' => 'Qtd. de produtos',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Esta marca não pode ser excluída pois há produtos associados.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Informações básicas',
        ],
        'products' => [
            'label' => 'Produtos',
            'actions' => [
                'attach' => [
                    'label' => 'Associar um produto',
                    'form' => [
                        'record_id' => [
                            'label' => 'Produto',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Produto associado à marca',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Produto desassociado.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Coleções',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Selecione uma coleção',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Associar uma coleção',
                ],
            ],
        ],
    ],

];
