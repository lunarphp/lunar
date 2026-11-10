<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'اربط مجموعات العملاء بطريقة الشحن هذه لتحديد توافرها.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'رسوم الشحن',
        'actions' => [
            'create' => [
                'label' => 'إنشاء سعر شحن',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'جميع الأسعار تشمل الضريبة، والتي سيتم أخذها في الاعتبار عند حساب الحد الأدنى للإنفاق.',
            'prices_excl_tax' => 'جميع الأسعار لا تشمل الضريبة، وسيتم حساب الحد الأدنى للإنفاق استنادًا إلى المجموع الفرعي للسلة.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'طريقة الشحن',
            ],
            'price' => [
                'label' => 'السعر',
            ],
            'prices' => [
                'label' => 'شرائح الأسعار',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'مجموعة العملاء',
                        'placeholder' => 'أي',
                    ],
                    'currency_id' => [
                        'label' => 'العملة',
                    ],
                    'min_spend' => [
                        'label' => 'الحد الأدنى للإنفاق',
                    ],
                    'min_weight' => [
                        'label' => 'الحد الأدنى للوزن',
                        'helper_text' => 'أدخل الوزن بوحدة :unit',
                    ],
                    'price' => [
                        'label' => 'السعر',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'مفعّل',
            ],
            'disabled' => [
                'label' => 'معطّل',
            ],
            'shipping_method' => [
                'label' => 'طريقة الشحن',
                'disabled' => 'معطّل',
            ],
            'price' => [
                'label' => 'السعر',
            ],
            'price_breaks_count' => [
                'label' => 'عدد الشرائح',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'استثناءات الشحن',
        'form' => [
            'purchasable' => [
                'label' => 'المنتج',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'إضافة قائمة استثناءات الشحن',
            ],
            'attach' => [
                'label' => 'إضافة قائمة استثناء',
            ],
            'detach' => [
                'label' => 'إزالة',
            ],
        ],
    ],
];
