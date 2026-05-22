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
        'schedule' => [
            'label' => 'Availability Schedule',
            'days' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'from' => [
                'label' => 'From',
            ],
            'to' => [
                'label' => 'Until',
                'validation' => [
                    'after' => 'The until time must be after the from time.',
                ],
            ],
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
        'weight_unit' => [
            'label' => 'Weight Unit',
            'placeholder' => 'No weight restriction',
        ],
        'min_weight' => [
            'label' => 'Minimum Weight',
        ],
        'max_weight' => [
            'label' => 'Maximum Weight',
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
