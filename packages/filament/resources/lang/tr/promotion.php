<?php

return [

    'label' => 'Promosyon',

    'plural_label' => 'Promosyonlar',

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'description' => [
            'label' => 'Açıklama',
        ],
        'starts_at' => [
            'label' => 'Başlangıç Tarihi',
        ],
        'ends_at' => [
            'label' => 'Bitiş Tarihi',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'discounts_count' => [
            'label' => 'İndirim Sayısı',
        ],
        'starts_at' => [
            'label' => 'Başlangıç Tarihi',
        ],
        'ends_at' => [
            'label' => 'Bitiş Tarihi',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'İndirimler',
            'description' => 'Bu kampanyaya ait indirimler.',
            'actions' => [
                'associate' => [
                    'label' => 'Bir indirim ilişkilendir',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'handle' => [
                    'label' => 'Tanımlayıcı',
                ],
                'status' => [
                    'label' => 'Durum',
                ],
                'starts_at' => [
                    'label' => 'Başlangıç Tarihi',
                ],
                'ends_at' => [
                    'label' => 'Bitiş Tarihi',
                ],
            ],
        ],
    ],

];
