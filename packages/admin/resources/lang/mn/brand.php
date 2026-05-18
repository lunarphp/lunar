<?php

return [

    'label' => 'Бренд',

    'plural_label' => 'Брендүүд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'products_count' => [
            'label' => 'Бүтээгдэхүүний тоо',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Энэ брендтэй бүтээгдэхүүнүүд холбогдсон тул устгах боломжгүй байна.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Үндсэн мэдээлэл',
        ],
        'products' => [
            'label' => 'Бүтээгдэхүүнүүд',
            'actions' => [
                'attach' => [
                    'label' => 'Бүтээгдэхүүн холбох',
                    'form' => [
                        'record_id' => [
                            'label' => 'Бүтээгдэхүүн',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Бүтээгдэхүүн брендэд холбогдсон',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Бүтээгдэхүүн салгагдсан.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Коллекцүүд',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Коллекц сонгох',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Коллекц холбох',
                ],
            ],
        ],
    ],

];
