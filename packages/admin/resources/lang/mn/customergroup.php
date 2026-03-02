<?php

return [

    'label' => 'Харилцагчийн бүлэг',

    'plural_label' => 'Харилцагчийн бүлгүүд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'default' => [
            'label' => 'Анхны',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'default' => [
            'label' => 'Анхны',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Энэ харилцагчийн бүлэгт харилцагчид холбогдсон тул устгах боломжгүй байна.',
            ],
        ],
    ],
];
