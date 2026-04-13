<?php

return [

    'label' => 'Колекция',

    'plural_label' => 'Колекции',

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Подколекции',
            'actions' => [
                'create_child' => [
                    'label' => 'Създаване на подколекция',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Брой подколекции',
                ],
                'name' => [
                    'label' => 'Име',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Основна информация',
        ],
        'products' => [
            'label' => 'Продукти',
            'actions' => [
                'attach' => [
                    'label' => 'Добавяне на продукт',
                ],
            ],
        ],
    ],

];
