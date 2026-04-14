<?php

return [

    'label' => 'نوع المنتج',

    'plural_label' => 'أنواع المنتجات',

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'products_count' => [
            'label' => 'عدد المنتجات',
        ],
        'product_attributes_count' => [
            'label' => 'سمات المنتج',
        ],
        'variant_attributes_count' => [
            'label' => 'سمات المتغير',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'سمات المنتج',
        ],
        'variant_attributes' => [
            'label' => 'سمات المتغير',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
    ],

    'attributes' => [
        'no_groups' => 'لا توجد مجموعات سمات متاحة.',
        'no_attributes' => 'لا توجد سمات متاحة.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'لا يمكن حذف هذا النوع من المنتجات لأنه مرتبط بمنتجات موجودة.',
            ],
        ],
    ],

];
