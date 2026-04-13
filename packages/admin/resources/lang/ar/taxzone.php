<?php

return [

    'label' => 'منطقة ضرائب',

    'plural_label' => 'مناطق الضرائب',

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'zone_type' => [
            'label' => 'نوع المنطقة',
        ],
        'active' => [
            'label' => 'نشط',
        ],
        'default' => [
            'label' => 'افتراضي',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'zone_type' => [
            'label' => 'نوع المنطقة',
            'options' => [
                'country' => 'تحديد حسب الدول',
                'states' => 'تحديد حسب المحافظات',
                'postcodes' => 'تحديد حسب الرموز البريدية',
            ],
        ],
        'price_display' => [
            'label' => 'عرض السعر',
            'options' => [
                'include_tax' => 'شامل الضريبة',
                'exclude_tax' => 'بدون ضريبة',
            ],
        ],
        'active' => [
            'label' => 'نشط',
        ],
        'default' => [
            'label' => 'افتراضي',
        ],

        'zone_countries' => [
            'label' => 'الدول',
        ],

        'zone_country' => [
            'label' => 'الدولة',
        ],

        'zone_states' => [
            'label' => 'المحافظات',
        ],

        'zone_postcodes' => [
            'label' => 'الرموز البريدية',
            'helper' => 'أدرج كل رمز بريدي في سطر جديد. يدعم الرموز العامة مثل NW*',
        ],

    ],

];
