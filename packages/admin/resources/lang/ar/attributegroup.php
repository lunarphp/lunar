<?php

return [

    'label' => 'مجموعة سمات',

    'plural_label' => 'مجموعات السمات',

    'table' => [
        'attributable_type' => [
            'label' => 'النوع',
        ],
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'position' => [
            'label' => 'الموضع',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'النوع',
        ],
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'position' => [
            'label' => 'الموضع',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'لا يمكن حذف مجموعة السمات هذه لأنها مرتبطة بسمات أخرى.',
            ],
        ],
    ],
];
