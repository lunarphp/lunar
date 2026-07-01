<?php

return [

    'label' => 'Промоция',

    'plural_label' => 'Промоции',

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'description' => [
            'label' => 'Описание',
        ],
        'starts_at' => [
            'label' => 'Начална дата',
        ],
        'ends_at' => [
            'label' => 'Крайна дата',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'discounts_count' => [
            'label' => 'Брой отстъпки',
        ],
        'starts_at' => [
            'label' => 'Начална дата',
        ],
        'ends_at' => [
            'label' => 'Крайна дата',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Отстъпки',
            'description' => 'Отстъпките, които принадлежат към тази кампания.',
            'actions' => [
                'associate' => [
                    'label' => 'Свързване на отстъпка',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'handle' => [
                    'label' => 'Код',
                ],
                'status' => [
                    'label' => 'Статус',
                ],
                'starts_at' => [
                    'label' => 'Начална дата',
                ],
                'ends_at' => [
                    'label' => 'Крайна дата',
                ],
            ],
        ],
    ],

];
