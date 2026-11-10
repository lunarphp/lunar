<?php

return [
    'customer_groups' => [
        'title' => 'مجموعات العملاء',
        'actions' => [
            'attach' => [
                'label' => 'إرفاق مجموعة عملاء',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'الاسم',
            ],
            'enabled' => [
                'label' => 'مفعل',
            ],
            'starts_at' => [
                'label' => 'تاريخ البدء',
            ],
            'ends_at' => [
                'label' => 'تاريخ الانتهاء',
            ],
            'visible' => [
                'label' => 'مرئي',
            ],
            'purchasable' => [
                'label' => 'قابل للشراء',
            ],
        ],
        'table' => [
            'description' => 'قم بارفاق مجموعات العملاء بهذا :type لتحديد مدى توفره.',
            'name' => [
                'label' => 'الاسم',
                'default_description' => 'افتراضي — يتحكم بوصول الزوار',
            ],
            'enabled' => [
                'label' => 'مفعل',
            ],
            'starts_at' => [
                'label' => 'تاريخ البدء',
            ],
            'ends_at' => [
                'label' => 'تاريخ الانتهاء',
            ],
            'visible' => [
                'label' => 'مرئي',
            ],
            'purchasable' => [
                'label' => 'قابل للشراء',
            ],
        ],
    ],
    'channels' => [
        'title' => 'واجهات البيع',
        'actions' => [
            'attach' => [
                'label' => 'جدولة واجهة بيع أخرى',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'مفعل',
                'helper_text_false' => 'لن يتم تفعيل واجهة البيع هذه حتى لو جاء تاريخ البدء.',
            ],
            'starts_at' => [
                'label' => 'تاريخ البدء',
                'helper_text' => 'اتركه فارغًا ليكون متاحًا من أي تاريخ.',
            ],
            'ends_at' => [
                'label' => 'تاريخ الانتهاء',
                'helper_text' => 'اتركه فارغًا ليكون متاحًا إلى أجل غير مسمى.',
            ],
        ],
        'table' => [
            'description' => 'حدد واجهات البيع المفعلة وقم بجدولة التوفر.',
            'name' => [
                'label' => 'الاسم',
            ],
            'enabled' => [
                'label' => 'مفعل',
            ],
            'starts_at' => [
                'label' => 'تاريخ البدء',
            ],
            'ends_at' => [
                'label' => 'تاريخ الانتهاء',
            ],
        ],
    ],
    'medias' => [
        'title' => 'صورة',
        'title_plural' => 'الصور',
        'actions' => [
            'attach' => [
                'label' => 'إرفاق صورة',
            ],
            'create' => [
                'label' => 'إنشاء صورة',
            ],
            'detach' => [
                'label' => 'إلغاء الإرفاق',
            ],
            'view' => [
                'label' => 'عرض',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'الاسم',
            ],
            'media' => [
                'label' => 'الصورة',
            ],
            'primary' => [
                'label' => 'الرئيسية',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'الصورة',
            ],
            'file' => [
                'label' => 'الملف',
            ],
            'name' => [
                'label' => 'الاسم',
            ],
            'primary' => [
                'label' => 'الرئيسية',
            ],
        ],
        'all_media_attached' => 'لا توجد صور للمنتج متاحة للإرفاق',
        'variant_description' => 'قم بإرفاق صور المنتج بهذا المتغير',
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'إنشاء URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'اللغة',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'المعرف المختصر',
            ],
            'default' => [
                'label' => 'افتراضي',
            ],
            'language' => [
                'label' => 'اللغة',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'المعرف المختصر',
            ],
            'default' => [
                'label' => 'افتراضي',
            ],
            'language' => [
                'label' => 'اللغة',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'تسعير مجموعة العملاء',
        'title_plural' => 'تسعير مجموعة العملاء',
        'table' => [
            'heading' => 'تسعير مجموعة العملاء',
            'description' => 'قم بربط السعر بمجموعات العملاء لتحديد سعر المنتج.',
            'empty_state' => [
                'label' => 'لا يوجد تسعير لمجموعات العملاء.',
                'description' => 'أنشئ سعرًا لمجموعة عملاء للبدء.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'إضافة سعر لمجموعة عملاء',
                    'modal' => [
                        'heading' => 'إنشاء سعر لمجموعة عملاء',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'السعر',
        'title_plural' => 'السعر',
        'tab_name' => 'شرائح الأسعار',
        'table' => [
            'heading' => 'شرائح الأسعار',
            'description' => 'خفض السعر عند شراء العميل كميات أكبر.',
            'empty_state' => [
                'label' => 'لا توجد شرائح سعرية.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'إضافة شريحة سعرية',
                ],
            ],
            'price' => [
                'label' => 'السعر',
            ],
            'customer_group' => [
                'label' => 'مجموعة العملاء',
                'placeholder' => 'جميع مجموعات العملاء',
            ],
            'min_quantity' => [
                'label' => 'الحد الأدنى للكمية',
            ],
            'currency' => [
                'label' => 'العملة',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'السعر',
                'helper_text' => 'سعر البيع قبل الخصومات.',
            ],
            'customer_group_id' => [
                'label' => 'مجموعة العملاء',
                'placeholder' => 'جميع مجموعات العملاء',
                'helper_text' => 'اختر مجموعة العملاء لتطبيق هذا السعر عليها.',
            ],
            'min_quantity' => [
                'label' => 'الحد الأدنى للكمية',
                'helper_text' => 'اختر الحد الأدنى للكمية التي سيكون هذا السعر متاحًا لها.',
                'validation' => [
                    'unique' => 'يجب أن تكون مجموعة العملاء والحد الأدنى للكمية فريدين.',
                ],
            ],
            'currency_id' => [
                'label' => 'العملة',
                'helper_text' => 'اختر العملة لهذا السعر.',
            ],
            'compare_price' => [
                'label' => 'التكلفة',
                'helper_text' => 'السعر الأصلي أو السعر المقترح للبيع، للمقارنة مع سعر البيع.',
            ],
            'basePrices' => [
                'title' => 'الاسعار',
                'form' => [
                    'price' => [
                        'label' => 'السعر',
                        'helper_text' => 'اختر الحد الأدنى للكمية التي سيكون هذا السعر متاحًا لها.',
                        'sync_price' => 'السعر متزامن مع العملة الافتراضية.',
                    ],
                    'compare_price' => [
                        'label' => 'التكلفة',
                        'helper_text' => 'السعر الأصلي أو السعر المقترح للبيع، للمقارنة مع سعر البيع.',
                    ],
                ],
                'tooltip' => 'تم إنشاؤه تلقائيًا بناءً على أسعار صرف العملات.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'النسبة المئوية',
            ],
            'tax_class' => [
                'label' => 'فئة الضريبة',
            ],
        ],
    ],
    'values' => [
        'title' => 'القيم',
        'form' => [
            'name' => [
                'label' => 'الاسم',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'الاسم',
            ],
            'position' => [
                'label' => 'الموضع',
            ],
        ],
    ],

];
