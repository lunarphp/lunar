<?php

return [

    'label' => 'Атрибутын бүлэг',

    'plural_label' => 'Атрибутын бүлгүүд',

    'table' => [
        'attributable_type' => [
            'label' => 'Төрөл',
        ],
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Байрлал',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Төрөл',
        ],
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Байрлал',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Энэ атрибутын бүлэгт атрибутууд холбогдсон тул устгах боломжгүй байна.',
            ],
        ],
    ],
];
