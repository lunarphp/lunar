<?php

return [

    'label' => 'Коллекцын бүлэг',

    'plural_label' => 'Коллекцын бүлгүүд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'collections_count' => [
            'label' => 'Коллекцийн тоо',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Энэ коллекцын бүлэгт коллекцүүд холбогдсон тул устгах боломжгүй байна.',
            ],
        ],
    ],
];
