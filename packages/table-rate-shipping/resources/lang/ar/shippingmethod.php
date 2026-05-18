<?php

return [
    'label_plural' => 'طرق الشحن',
    'label' => 'طريقة الشحن',
    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'description' => [
            'label' => 'الوصف',
        ],
        'code' => [
            'label' => 'الرمز',
        ],
        'cutoff' => [
            'label' => 'الحد الأقصى',
        ],
        'charge_by' => [
            'label' => 'احتساب بناءً على',
            'options' => [
                'cart_total' => 'إجمالي السلة',
                'weight' => 'الوزن',
            ],
        ],
        'driver' => [
            'label' => 'النوع',
            'options' => [
                'ship-by' => 'قياسي',
                'collection' => 'استلام من المتجر',
            ],
        ],
        'stock_available' => [
            'label' => 'يجب توفر مخزون جميع عناصر السلة',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'code' => [
            'label' => 'الرمز',
        ],
        'driver' => [
            'label' => 'النوع',
            'options' => [
                'ship-by' => 'قياسي',
                'collection' => 'استلام من المتجر',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'التوفر',
            'customer_groups' => 'طريقة الشحن هذه غير متاحة حاليًا لجميع مجموعات العملاء.',
        ],
    ],
];
