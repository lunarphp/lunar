<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'إنشاء تشكيلة رئيسية',
        ],
        'create_child' => [
            'label' => 'إنشاء تشكيلة فرعية',
        ],
        'move' => [
            'label' => 'نقل التشكيلة',
        ],
        'delete' => [
            'label' => 'حذف',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'لا يمكن الحذف',
                    'body' => 'تحتوي هذه التشكيلة على تشكيلات فرعية ولا يمكن حذفها.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'تحديث الحالة',
            'wizard' => [
                'step_one' => [
                    'label' => 'الحالة',
                ],
                'step_two' => [
                    'label' => 'البريد والتنبيهات',
                    'no_mailers' => 'لا توجد قوالب بريد متاحة لهذه الحالة.',
                ],
                'step_three' => [
                    'label' => 'المعاينة والحفظ',
                    'no_mailers' => 'لم يتم اختيار أي قوالب بريد للمعاينة.',
                ],
            ],
            'notification' => [
                'label' => 'تم تحديث حالة الطلب',
            ],
            'billing_email' => [
                'label' => 'بريد الفوترة الإلكتروني',
            ],
            'shipping_email' => [
                'label' => 'بريد الشحن الإلكتروني',
            ],
        ],

    ],
];
