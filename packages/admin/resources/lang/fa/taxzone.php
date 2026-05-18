<?php

return [

    'label' => 'منطقه مالیاتی',

    'plural_label' => 'مناطق مالیاتی',

    'table' => [
        'name' => [
            'label' => 'نام',
        ],
        'zone_type' => [
            'label' => 'نوع منطقه',
        ],
        'active' => [
            'label' => 'فعال',
        ],
        'default' => [
            'label' => 'پیش‌فرض',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'نام',
        ],
        'zone_type' => [
            'label' => 'نوع منطقه',
            'options' => [
                'country' => 'محدود به کشورها',
                'states' => 'محدود به استان‌ها',
                'postcodes' => 'محدود به کدهای پستی',
            ],
        ],
        'price_display' => [
            'label' => 'نمایش قیمت',
            'options' => [
                'include_tax' => 'شامل مالیات',
                'exclude_tax' => 'بدون مالیات',
            ],
        ],
        'active' => [
            'label' => 'فعال',
        ],
        'default' => [
            'label' => 'پیش‌فرض',
        ],

        'zone_countries' => [
            'label' => 'کشورها',
        ],

        'zone_country' => [
            'label' => 'کشور',
        ],

        'zone_states' => [
            'label' => 'استان‌ها',
        ],

        'zone_postcodes' => [
            'label' => 'کدهای پستی',
            'helper' => 'هر کد پستی را در یک خط جداگانه وارد کنید. از کاراکترهای عام مثل NW* پشتیبانی می‌شود.',
        ],

    ],

];
