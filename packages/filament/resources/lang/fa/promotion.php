<?php

return [

    'label' => 'کمپین',

    'plural_label' => 'کمپین‌ها',

    'form' => [
        'name' => [
            'label' => 'نام',
        ],
        'handle' => [
            'label' => 'شناسه',
        ],
        'description' => [
            'label' => 'توضیحات',
        ],
        'starts_at' => [
            'label' => 'تاریخ شروع',
        ],
        'ends_at' => [
            'label' => 'تاریخ پایان',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'نام',
        ],
        'handle' => [
            'label' => 'شناسه',
        ],
        'discounts_count' => [
            'label' => 'تعداد تخفیف‌ها',
        ],
        'starts_at' => [
            'label' => 'تاریخ شروع',
        ],
        'ends_at' => [
            'label' => 'تاریخ پایان',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'تخفیف‌ها',
            'description' => 'تخفیف‌هایی که به این کمپین تعلق دارند.',
            'actions' => [
                'associate' => [
                    'label' => 'مرتبط‌کردن تخفیف',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'نام',
                ],
                'handle' => [
                    'label' => 'شناسه',
                ],
                'status' => [
                    'label' => 'وضعیت',
                ],
                'starts_at' => [
                    'label' => 'تاریخ شروع',
                ],
                'ends_at' => [
                    'label' => 'تاریخ پایان',
                ],
            ],
        ],
    ],

];
