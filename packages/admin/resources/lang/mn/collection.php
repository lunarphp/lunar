<?php

return [

    'label' => 'Коллекц',

    'plural_label' => 'Коллекцүүд',

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Дэд коллекцүүд',
            'actions' => [
                'create_child' => [
                    'label' => 'Дэд коллекц үүсгэх',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Дэд коллекцийн тоо',
                ],
                'name' => [
                    'label' => 'Нэр',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Үндсэн мэдээлэл',
        ],
        'products' => [
            'label' => 'Бүтээгдэхүүнүүд',
            'actions' => [
                'attach' => [
                    'label' => 'Бүтээгдэхүүн холбох',
                ],
            ],
        ],
    ],

];
