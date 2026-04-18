<?php

return [

    'label' => 'مجموعة تشكيلات',

    'plural_label' => 'مجموعات التشكيلات',

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'collections_count' => [
            'label' => 'عدد التشكيلات',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'لا يمكن حذف هذه المجموعة لأنها تحتوي على تشكيلات مرتبطة.',
            ],
        ],
    ],
];
