<?php

return [
    'label_plural' => 'روش‌های ارسال',
    'label' => 'روش ارسال',
    'form' => [
        'name' => [
            'label' => 'نام',
        ],
        'description' => [
            'label' => 'توضیحات',
        ],
        'code' => [
            'label' => 'کد',
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
            'label' => 'محاسبه بر اساس',
            'options' => [
                'cart_total' => 'جمع کل سبد خرید',
                'weight' => 'وزن',
            ],
        ],
        'driver' => [
            'label' => 'نوع',
            'options' => [
                'ship-by' => 'استاندارد',
                'collection' => 'تحویل حضوری',
            ],
        ],
        'stock_available' => [
            'label' => 'موجودی همه اقلام سبد باید در دسترس باشد',
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
            'label' => 'نام',
        ],
        'code' => [
            'label' => 'کد',
        ],
        'driver' => [
            'label' => 'نوع',
            'options' => [
                'ship-by' => 'استاندارد',
                'collection' => 'تحویل حضوری',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'دسترس‌پذیری',
            'customer_groups' => 'این روش ارسال در حال حاضر برای هیچ‌یک از گروه‌های مشتری در دسترس نیست.',
        ],
    ],
];
