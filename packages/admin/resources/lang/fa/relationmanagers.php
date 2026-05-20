<?php

return [
    'customer_groups' => [
        'title' => 'گروه‌های مشتری',
        'actions' => [
            'attach' => [
                'label' => 'اتصال گروه مشتری',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'نام',
            ],
            'enabled' => [
                'label' => 'فعال',
            ],
            'starts_at' => [
                'label' => 'تاریخ شروع',
            ],
            'ends_at' => [
                'label' => 'تاریخ پایان',
            ],
            'visible' => [
                'label' => 'قابل مشاهده',
            ],
            'purchasable' => [
                'label' => 'قابل خرید',
            ],
        ],
        'table' => [
            'description' => 'گروه‌های مشتری را به این :type متصل کنید تا در دسترس بودن آن مشخص شود.',
            'name' => [
                'label' => 'نام',
                'default_description' => 'پیش‌فرض — دسترسی مهمانان را کنترل می‌کند',
            ],
            'enabled' => [
                'label' => 'فعال',
            ],
            'starts_at' => [
                'label' => 'تاریخ شروع',
            ],
            'ends_at' => [
                'label' => 'تاریخ پایان',
            ],
            'visible' => [
                'label' => 'قابل مشاهده',
            ],
            'purchasable' => [
                'label' => 'قابل خرید',
            ],
        ],
    ],
    'channels' => [
        'title' => 'کانال‌ها',
        'actions' => [
            'attach' => [
                'label' => 'زمان‌بندی یک کانال دیگر',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'فعال',
                'helper_text_false' => 'این کانال حتی اگر تاریخ شروع تعیین شده باشد فعال نخواهد شد.',
            ],
            'starts_at' => [
                'label' => 'تاریخ شروع',
                'helper_text' => 'برای در دسترس بودن از هر تاریخی، خالی بگذارید.',
            ],
            'ends_at' => [
                'label' => 'تاریخ پایان',
                'helper_text' => 'برای در دسترس بودن بدون محدودیت، خالی بگذارید.',
            ],
        ],
        'table' => [
            'description' => 'مشخص کنید کدام کانال‌ها فعال هستند و در چه بازه‌ای در دسترس باشند.',
            'name' => [
                'label' => 'نام',
            ],
            'enabled' => [
                'label' => 'فعال',
            ],
            'starts_at' => [
                'label' => 'تاریخ شروع',
            ],
            'ends_at' => [
                'label' => 'تاریخ پایان',
            ],
        ],
    ],
    'medias' => [
        'title' => 'رسانه',
        'title_plural' => 'رسانه‌ها',
        'actions' => [
            'attach' => [
                'label' => 'اتصال رسانه',
            ],
            'create' => [
                'label' => 'ایجاد رسانه',
            ],
            'detach' => [
                'label' => 'جدا کردن',
            ],
            'view' => [
                'label' => 'مشاهده',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'نام',
            ],
            'media' => [
                'label' => 'تصویر',
            ],
            'primary' => [
                'label' => 'اصلی',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'تصویر',
            ],
            'file' => [
                'label' => 'فایل',
            ],
            'name' => [
                'label' => 'نام',
            ],
            'primary' => [
                'label' => 'اصلی',
            ],
        ],
        'all_media_attached' => 'هیچ تصویر محصولی برای اتصال موجود نیست',
        'variant_description' => 'تصاویر محصول را به این واریانت متصل کنید',
    ],
    'urls' => [
        'title' => 'آدرس',
        'title_plural' => 'آدرس‌ها',
        'actions' => [
            'create' => [
                'label' => 'ایجاد آدرس',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'زبان',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'اسلاگ',
            ],
            'default' => [
                'label' => 'پیش‌فرض',
            ],
            'language' => [
                'label' => 'زبان',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'اسلاگ',
            ],
            'default' => [
                'label' => 'پیش‌فرض',
            ],
            'language' => [
                'label' => 'زبان',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'قیمت‌گذاری گروه مشتری',
        'title_plural' => 'قیمت‌گذاری‌های گروه مشتری',
        'table' => [
            'heading' => 'قیمت‌گذاری گروه مشتری',
            'description' => 'قیمت‌ها را به گروه‌های مشتری متصل کنید تا قیمت محصول تعیین شود.',
            'empty_state' => [
                'label' => 'هیچ قیمت‌گذاری گروه مشتری‌ای وجود ندارد.',
                'description' => 'برای شروع یک قیمت گروه مشتری ایجاد کنید.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'افزودن قیمت گروه مشتری',
                    'modal' => [
                        'heading' => 'ایجاد قیمت گروه مشتری',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'قیمت‌گذاری',
        'title_plural' => 'قیمت‌گذاری‌ها',
        'tab_name' => 'سطوح قیمتی',
        'table' => [
            'heading' => 'سطوح قیمتی',
            'description' => 'وقتی مشتری در تعداد بالاتر خرید می‌کند قیمت را کاهش دهید.',
            'empty_state' => [
                'label' => 'هیچ سطح قیمتی‌ای وجود ندارد.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'افزودن سطح قیمت',
                ],
            ],
            'price' => [
                'label' => 'قیمت',
            ],
            'customer_group' => [
                'label' => 'گروه مشتری',
                'placeholder' => 'همه گروه‌های مشتری',
            ],
            'min_quantity' => [
                'label' => 'حداقل تعداد',
            ],
            'currency' => [
                'label' => 'ارز',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'قیمت',
                'helper_text' => 'قیمت خرید قبل از اعمال تخفیف‌ها.',
            ],
            'customer_group_id' => [
                'label' => 'گروه مشتری',
                'placeholder' => 'همه گروه‌های مشتری',
                'helper_text' => 'انتخاب کنید این قیمت برای کدام گروه مشتری اعمال شود.',
            ],
            'min_quantity' => [
                'label' => 'حداقل تعداد',
                'helper_text' => 'حداقل تعدادی که این قیمت برای آن در دسترس است را انتخاب کنید.',
                'validation' => [
                    'unique' => 'گروه مشتری و حداقل تعداد باید یکتا باشند.',
                ],
            ],
            'currency_id' => [
                'label' => 'ارز',
                'helper_text' => 'ارز این قیمت را انتخاب کنید.',
            ],
            'compare_price' => [
                'label' => 'قیمت مقایسه‌ای',
                'helper_text' => 'قیمت اصلی یا قیمت مصرف‌کننده (RRP) برای مقایسه با قیمت خرید.',
            ],
            'basePrices' => [
                'title' => 'قیمت‌ها',
                'form' => [
                    'price' => [
                        'label' => 'قیمت',
                        'helper_text' => 'قیمت خرید قبل از تخفیف‌ها.',
                        'sync_price' => 'قیمت با ارز پیش‌فرض همگام‌سازی شده است.',
                    ],
                    'compare_price' => [
                        'label' => 'قیمت مقایسه‌ای',
                        'helper_text' => 'قیمت اصلی یا قیمت مصرف‌کننده (RRP) برای مقایسه با قیمت خرید.',
                    ],
                ],
                'tooltip' => 'به صورت خودکار بر اساس نرخ‌های تبدیل ارز تولید می‌شود.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'درصد',
            ],
            'tax_class' => [
                'label' => 'کلاس مالیاتی',
            ],
        ],
    ],
    'values' => [
        'title' => 'مقادیر',
        'form' => [
            'name' => [
                'label' => 'نام',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'نام',
            ],
            'position' => [
                'label' => 'موقعیت',
            ],
        ],
    ],

];
