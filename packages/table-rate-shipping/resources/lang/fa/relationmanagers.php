<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'گروه‌های مشتری را به این روش ارسال مرتبط کنید تا در دسترس بودن آن مشخص شود.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'نرخ‌های ارسال',
        'actions' => [
            'create' => [
                'label' => 'ایجاد نرخ ارسال',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'همه قیمت‌ها شامل مالیات هستند و هنگام محاسبه حداقل مبلغ خرید در نظر گرفته می‌شوند.',
            'prices_excl_tax' => 'همه قیمت‌ها بدون مالیات هستند و حداقل مبلغ خرید بر اساس جمع جزء سبد خرید محاسبه می‌شود.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'روش ارسال',
            ],
            'price' => [
                'label' => 'قیمت',
            ],
            'prices' => [
                'label' => 'شکست قیمت‌ها',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'گروه مشتری',
                        'placeholder' => 'همه',
                    ],
                    'currency_id' => [
                        'label' => 'ارز',
                    ],
                    'min_spend' => [
                        'label' => 'حداقل مبلغ خرید',
                    ],
                    'min_weight' => [
                        'label' => 'حداقل وزن',
                        'helper_text' => 'وزن را به :unit وارد کنید',
                    ],
                    'price' => [
                        'label' => 'قیمت',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'فعال',
            ],
            'disabled' => [
                'label' => 'غیرفعال',
            ],
            'shipping_method' => [
                'label' => 'روش ارسال',
                'disabled' => 'غیرفعال',
            ],
            'price' => [
                'label' => 'قیمت',
            ],
            'price_breaks_count' => [
                'label' => 'شکست قیمت‌ها',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'استثناهای ارسال',
        'form' => [
            'purchasable' => [
                'label' => 'محصول',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'افزودن لیست استثنای ارسال',
            ],
            'attach' => [
                'label' => 'افزودن لیست استثنا',
            ],
            'detach' => [
                'label' => 'حذف',
            ],
        ],
    ],
];
