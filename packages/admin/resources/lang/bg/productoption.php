<?php

return [

    'label' => 'Продуктова опция',

    'plural_label' => 'Продуктови опции',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'label' => [
            'label' => 'Етикет',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'shared' => [
            'label' => 'Споделена',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'label' => [
            'label' => 'Етикет',
        ],
        'handle' => [
            'label' => 'Код',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Вариантите на продукта са запазени',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Отказ',
                ],
                'save-options' => [
                    'label' => 'Запази опциите',
                ],
                'add-shared-option' => [
                    'label' => 'Добави споделена опция',
                    'form' => [
                        'product_option' => [
                            'label' => 'Продуктова опция',
                        ],
                        'no_shared_components' => [
                            'label' => 'Няма налични споделени опции.',
                        ],
                        'preselect' => [
                            'label' => 'Предварително избери всички стойности по подразбиране.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Добави опция',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Няма конфигурирани продуктови опции',
                    'description' => 'Добавете споделена или ограничена продуктова опция, за да започнете да генерирате варианти.',
                ],
            ],
            'options-table' => [
                'title' => 'Продуктови опции',
                'configure-options' => [
                    'label' => 'Конфигурирай опциите',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Опция',
                    ],
                    'values' => [
                        'label' => 'Стойности',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Продуктови варианти',
                'actions' => [
                    'create' => [
                        'label' => 'Създай вариант',
                    ],
                    'edit' => [
                        'label' => 'Редактирай',
                    ],
                    'delete' => [
                        'label' => 'Изтрий',
                    ],
                ],
                'empty' => [
                    'heading' => 'Няма конфигурирани варианти',
                ],
                'table' => [
                    'new' => [
                        'label' => 'НОВ',
                    ],
                    'option' => [
                        'label' => 'Опция',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Цена',
                    ],
                    'stock' => [
                        'label' => 'Наличност',
                    ],
                ],
            ],
        ],
    ],

];
