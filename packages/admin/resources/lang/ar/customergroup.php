<?php

return [

    'label' => 'مجموعة عملاء',

    'plural_label' => 'مجموعات العملاء',

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'default' => [
            'label' => 'افتراضي',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'default' => [
            'label' => 'افتراضي',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'لا يمكن حذف مجموعة العملاء هذه لأنه يوجد عملاء مرتبطون بها.',
            ],
        ],
    ],
];
