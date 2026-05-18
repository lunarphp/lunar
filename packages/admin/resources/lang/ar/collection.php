<?php

return [

    'label' => 'تشكيلة',

    'plural_label' => 'التشكيلات',

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'التشكيلات الفرعية',
            'actions' => [
                'create_child' => [
                    'label' => 'إنشاء تشكيلة فرعية',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'عدد التشكيلات الفرعية',
                ],
                'name' => [
                    'label' => 'الاسم',
                ],
            ],
        ],
        'edit' => [
            'label' => 'المعلومات الأساسية',
        ],
        'products' => [
            'label' => 'المنتجات',
            'actions' => [
                'attach' => [
                    'label' => 'إرفاق منتج',
                ],
            ],
        ],
    ],

];
