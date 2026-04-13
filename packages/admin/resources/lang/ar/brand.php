<?php

return [

    'label' => 'علامة تجارية',

    'plural_label' => 'العلامات التجارية',

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'products_count' => [
            'label' => 'عدد المنتجات',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'لا يمكن حذف هذه العلامة التجارية لأنه توجد منتجات مرتبطة بها.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'المعلومات الأساسية',
        ],
        'products' => [
            'label' => 'المنتجات',
            'actions' => [
                'attach' => [
                    'label' => 'ربط منتج',
                    'form' => [
                        'record_id' => [
                            'label' => 'المنتج',
                        ],
                    ],
                    'notification' => [
                        'success' => 'تم ربط المنتج بالعلامة التجارية',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'تم إزالة المنتج.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'التشكيلات',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'اختر تشكيلة',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'ربط تشكيلة',
                ],
            ],
        ],
    ],

];
