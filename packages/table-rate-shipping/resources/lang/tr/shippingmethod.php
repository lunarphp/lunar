<?php

return [
    'label_plural' => 'Kargo Yöntemleri',
    'label' => 'Kargo Yöntemi',
    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'description' => [
            'label' => 'Açıklama',
        ],
        'code' => [
            'label' => 'Kod',
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
            'label' => 'Ücretlendirme Ölçütü',
            'options' => [
                'cart_total' => 'Sepet Toplamı',
                'weight' => 'Ağırlık',
            ],
        ],
        'driver' => [
            'label' => 'Tür',
            'options' => [
                'ship-by' => 'Standart',
                'collection' => 'Mağazadan Teslim Alma',
            ],
        ],
        'stock_available' => [
            'label' => 'Tüm sepet öğelerinin stoku mevcut olmalı',
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
            'label' => 'Ad',
        ],
        'code' => [
            'label' => 'Kod',
        ],
        'driver' => [
            'label' => 'Tür',
            'options' => [
                'ship-by' => 'Standart',
                'collection' => 'Mağazadan Teslim Alma',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Kullanılabilirlik',
            'customer_groups' => 'Bu kargo yöntemi şu anda tüm müşteri grupları için mevcut değil.',
        ],
    ],
];
