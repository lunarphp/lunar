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
        'cutoff' => [
            'label' => 'مهلت',
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
