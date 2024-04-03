<?php

return [

    'label' => 'Marca',

    'plural_label' => 'Marcas',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'products_count' => [
            'label' => 'Nº de Produtos',
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
];
