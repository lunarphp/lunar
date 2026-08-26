<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Энэ хүргэлтийн аргын боломжтой эсэхийг тодорхойлохын тулд хэрэглэгчийн бүлгүүдийг холбоно уу.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Хүргэлтийн үнэ',
        'actions' => [
            'create' => [
                'label' => 'Хүргэлтийн үнэ үүсгэх',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Бүх үнэ нь татвар багтсан бөгөөд хамгийн бага зарцуулалтыг тооцохдоо үүнийг харгалзан үзнэ.',
            'prices_excl_tax' => 'Бүх үнэ нь татваргүй бөгөөд хамгийн бага зарцуулалт нь сагсны нийт дүнд тулгуурлана.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Хүргэлтийн арга',
            ],
            'price' => [
                'label' => 'Үнэ',
            ],
            'prices' => [
                'label' => 'Үнийн зэрэглэл',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Хэрэглэгчийн бүлэг',
                        'placeholder' => 'Аливаа',
                    ],
                    'currency_id' => [
                        'label' => 'Валют',
                    ],
                    'min_spend' => [
                        'label' => 'Хамгийн бага зарцуулалт',
                    ],
                    'min_weight' => [
                        'label' => 'Хамгийн бага жин',
                        'helper_text' => 'Жинг :unit-ээр оруулна уу',
                    ],
                    'price' => [
                        'label' => 'Үнэ',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'Идэвхтэй',
            ],
            'disabled' => [
                'label' => 'идэвхгүй',
            ],
            'shipping_method' => [
                'label' => 'Хүргэлтийн арга',
                'disabled' => 'Идэвхгүй',
            ],
            'price' => [
                'label' => 'Үнэ',
            ],
            'price_breaks_count' => [
                'label' => 'Үнийн зэрэглэл',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Хүргэлтийн хязгаарлалт',
        'form' => [
            'purchasable' => [
                'label' => 'Бүтээгдэхүүн',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Хүргэлтийн хязгаарлалтын жагсаалт нэмэх',
            ],
            'attach' => [
                'label' => 'Хязгаарлалтын жагсаалт нэмэх',
            ],
            'detach' => [
                'label' => 'Хасах',
            ],
        ],
    ],
];
