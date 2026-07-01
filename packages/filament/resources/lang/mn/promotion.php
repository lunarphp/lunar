<?php

return [

    'label' => 'Урамшуулал',

    'plural_label' => 'Урамшууллууд',

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'description' => [
            'label' => 'Тайлбар',
        ],
        'starts_at' => [
            'label' => 'Эхлэх огноо',
        ],
        'ends_at' => [
            'label' => 'Дуусах огноо',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'discounts_count' => [
            'label' => 'Хөнгөлөлтийн тоо',
        ],
        'starts_at' => [
            'label' => 'Эхлэх огноо',
        ],
        'ends_at' => [
            'label' => 'Дуусах огноо',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Хөнгөлөлтүүд',
            'description' => 'Энэ кампанит ажилд хамаарах хөнгөлөлтүүд.',
            'actions' => [
                'associate' => [
                    'label' => 'Хөнгөлөлт холбох',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'handle' => [
                    'label' => 'Handle',
                ],
                'status' => [
                    'label' => 'Статус',
                ],
                'starts_at' => [
                    'label' => 'Эхлэх огноо',
                ],
                'ends_at' => [
                    'label' => 'Дуусах огноо',
                ],
            ],
        ],
    ],

];
