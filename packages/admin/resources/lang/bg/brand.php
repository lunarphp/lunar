<?php

return [

    'label' => 'Марка',

    'plural_label' => 'Марките',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'products_count' => [
            'label' => 'Брой продукти',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Тази марка не може да бъде изтрита, тъй като има свързани продукти.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Основна информация',
        ],
        'products' => [
            'label' => 'Продукти',
            'actions' => [
                'attach' => [
                    'label' => 'Асоцииране на продукт',
                    'form' => [
                        'record_id' => [
                            'label' => 'Продукт',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Продуктът е асоцииран към марката',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Продуктът е отделен.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Колекции',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Изберете колекция',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Асоцииране на колекция',
                ],
            ],
        ],
    ],

];
