<?php

return [
    'label_plural' => 'Хүргэлтийн аргууд',
    'label' => 'Хүргэлтийн арга',
    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'description' => [
            'label' => 'Тайлбар',
        ],
        'code' => [
            'label' => 'Код',
        ],
        'schedule' => [
            'label' => 'Боломжит хуваарь',
            'days' => [
                'monday' => 'Даваа',
                'tuesday' => 'Мягмар',
                'wednesday' => 'Лхагва',
                'thursday' => 'Пүрэв',
                'friday' => 'Баасан',
                'saturday' => 'Бямба',
                'sunday' => 'Ням',
            ],
            'from' => [
                'label' => 'Эхлэх',
            ],
            'to' => [
                'label' => 'Хүртэл',
                'validation' => [
                    'after' => 'Дуусах цаг нь эхлэх цагаас хойш байх ёстой.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Төлбөрийн төрөл',
            'options' => [
                'cart_total' => 'Сагсны нийт дүн',
                'weight' => 'Жин',
            ],
        ],
        'driver' => [
            'label' => 'Төрөл',
            'options' => [
                'ship-by' => 'Стандарт',
                'collection' => 'Цуглуулга',
            ],
        ],
        'stock_available' => [
            'label' => 'Сагсан дахь бүх барааны нөөц байх ёстой',
        ],
        'weight_unit' => [
            'label' => 'Жингийн нэгж',
            'placeholder' => 'Жингийн хязгаарлалтгүй',
        ],
        'min_weight' => [
            'label' => 'Хамгийн бага жин',
        ],
        'max_weight' => [
            'label' => 'Хамгийн их жин',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'code' => [
            'label' => 'Код',
        ],
        'driver' => [
            'label' => 'Төрөл',
            'options' => [
                'ship-by' => 'Стандарт',
                'collection' => 'Цуглуулга',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Боломжит байдал',
            'customer_groups' => 'Энэ хүргэлтийн арга нь бүх хэрэглэгчийн бүлэгт одоогоор боломжгүй байна.',
        ],
    ],
];
