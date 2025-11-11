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
        'cutoff' => [
            'label' => 'Son Sipariş Saati',
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
