<?php

return [

    'label' => 'Бүтээгдэхүүний сонголт',

    'plural_label' => 'Бүтээгдэхүүний сонголтууд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'label' => [
            'label' => 'Шошго',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'shared' => [
            'label' => 'Хуваалцсан',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'label' => [
            'label' => 'Шошго',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Бүтээгдэхүүний вариант хадгалагдсан',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Цуцлах',
                ],
                'save-options' => [
                    'label' => 'Сонголтууд хадгалах',
                ],
                'add-shared-option' => [
                    'label' => 'Хуваалцсан сонголт нэмэх',
                    'form' => [
                        'product_option' => [
                            'label' => 'Бүтээгдэхүүний сонголт',
                        ],
                        'no_shared_components' => [
                            'label' => 'Хуваалцсан сонголтууд байхгүй байна.',
                        ],
                        'preselect' => [
                            'label' => 'Бүх утгуудыг анхнаасаа сонгоно.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Сонголт нэмэх',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Бүтээгдэхүүний сонголтууд тохируулагдаагүй байна',
                    'description' => 'Вариант үүсгэхээс өмнө хуваалцсан эсвэл хязгаарласан бүтээгдэхүүний сонголт нэмнэ үү.',
                ],
            ],
            'options-table' => [
                'title' => 'Бүтээгдэхүүний сонголтууд',
                'configure-options' => [
                    'label' => 'Сонголтууд тохируулах',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Сонголт',
                    ],
                    'values' => [
                        'label' => 'Утгууд',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Бүтээгдэхүүний вариантүүд',
                'actions' => [
                    'create' => [
                        'label' => 'Вариант үүсгэх',
                    ],
                    'edit' => [
                        'label' => 'Засах',
                    ],
                    'delete' => [
                        'label' => 'Устгах',
                    ],
                ],
                'empty' => [
                    'heading' => 'Вариант тохируулагдаагүй байна',
                ],
                'table' => [
                    'new' => [
                        'label' => 'ШИНЭ',
                    ],
                    'option' => [
                        'label' => 'Сонголт',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Үнэ',
                    ],
                    'stock' => [
                        'label' => 'Нөөц',
                    ],
                ],
            ],
        ],
    ],

];
