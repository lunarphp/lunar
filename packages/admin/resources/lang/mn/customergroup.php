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
            'label' => 'Өгөгдмөл',
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
            'label' => 'Өгөгдмөл',
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
