<?php

return [

    'label' => 'حملة ترويجية',

    'plural_label' => 'الحملات الترويجية',

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'description' => [
            'label' => 'الوصف',
        ],
        'starts_at' => [
            'label' => 'تاريخ البداية',
        ],
        'ends_at' => [
            'label' => 'تاريخ الانتهاء',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'discounts_count' => [
            'label' => 'عدد الخصومات',
        ],
        'starts_at' => [
            'label' => 'تاريخ البداية',
        ],
        'ends_at' => [
            'label' => 'تاريخ الانتهاء',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'الخصومات',
            'description' => 'الخصومات التابعة لهذه الحملة.',
            'actions' => [
                'associate' => [
                    'label' => 'ربط خصم',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'handle' => [
                    'label' => 'المعرف',
                ],
                'status' => [
                    'label' => 'الحالة',
                ],
                'starts_at' => [
                    'label' => 'تاريخ البداية',
                ],
                'ends_at' => [
                    'label' => 'تاريخ الانتهاء',
                ],
            ],
        ],
    ],

];
