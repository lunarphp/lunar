<?php

return [
    'label_plural' => 'Методи за доставка',
    'label' => 'Метод за доставка',
    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'description' => [
            'label' => 'Описание',
        ],
        'code' => [
            'label' => 'Код',
        ],
        'schedule' => [
            'label' => 'Availability Schedule',
            'days' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'from' => [
                'label' => 'From',
            ],
            'to' => [
                'label' => 'Until',
                'validation' => [
                    'after' => 'The until time must be after the from time.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Таксуване по',
            'options' => [
                'cart_total' => 'Обща стойност на количката',
                'weight' => 'Тегло',
            ],
        ],
        'driver' => [
            'label' => 'Тип',
            'options' => [
                'ship-by' => 'Стандартен',
                'collection' => 'Колекция',
            ],
        ],
        'stock_available' => [
            'label' => 'Наличността на всички артикули в количката трябва да е налична',
        ],
        'weight_unit' => [
            'label' => 'Weight Unit',
            'placeholder' => 'No weight restriction',
        ],
        'min_weight' => [
            'label' => 'Minimum Weight',
        ],
        'max_weight' => [
            'label' => 'Maximum Weight',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'code' => [
            'label' => 'Код',
        ],
        'driver' => [
            'label' => 'Тип',
            'options' => [
                'ship-by' => 'Стандартен',
                'collection' => 'Колекция',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Наличност',
            'customer_groups' => 'Този метод за доставка в момента не е наличен за всички клиентски групи.',
        ],
    ],
];
