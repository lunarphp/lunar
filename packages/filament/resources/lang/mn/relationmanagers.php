<?php

return [
    'customer_groups' => [
        'title' => 'Харилцагчийн бүлгүүд',
        'actions' => [
            'attach' => [
                'label' => 'Харилцагчийн бүлэг холбох',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Нэр',
            ],
            'enabled' => [
                'label' => 'Идэвхжүүлсэн',
            ],
            'starts_at' => [
                'label' => 'Эхлэх огноо',
            ],
            'ends_at' => [
                'label' => 'Дуусах огноо',
            ],
            'visible' => [
                'label' => 'Харагдах',
            ],
            'purchasable' => [
                'label' => 'Худалдаж авах боломжтой',
            ],
        ],
        'table' => [
            'description' => 'Энэ :type-д харилцагчийн бүлгүүдийг холбоно уу түүний боломжийг тодорхойлохын тулд.',
            'name' => [
                'label' => 'Нэр',
                'default_description' => 'Үндсэн — зочдын хандалтыг удирдана',
            ],
            'enabled' => [
                'label' => 'Идэвхжүүлсэн',
            ],
            'starts_at' => [
                'label' => 'Эхлэх огноо',
            ],
            'ends_at' => [
                'label' => 'Дуусах огноо',
            ],
            'visible' => [
                'label' => 'Харагдах',
            ],
            'purchasable' => [
                'label' => 'Худалдаж авах боломжтой',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Сувгууд',
        'actions' => [
            'attach' => [
                'label' => 'Өөр суваг товлох',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Идэвхжүүлсэн',
                'helper_text_false' => 'Энэ суваг эхлэх огноо байсан ч идэвхжүүлэгдэхгүй.',
            ],
            'starts_at' => [
                'label' => 'Эхлэх огноо',
                'helper_text' => 'Ямар ч огнооноос боломжтой байлгахын тулд хоосон орхи.',
            ],
            'ends_at' => [
                'label' => 'Дуусах огноо',
                'helper_text' => 'Хязгааргүй боломжтой байлгахын тулд хоосон орхи.',
            ],
        ],
        'table' => [
            'description' => 'Ямар сувгууд идэвхжүүлсэн болон боломжийг товлох.',
            'name' => [
                'label' => 'Нэр',
            ],
            'enabled' => [
                'label' => 'Идэвхжүүлсэн',
            ],
            'starts_at' => [
                'label' => 'Эхлэх огноо',
            ],
            'ends_at' => [
                'label' => 'Дуусах огноо',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Медиа',
        'title_plural' => 'Медиа',
        'actions' => [
            'attach' => [
                'label' => 'Медиа холбох',
            ],
            'create' => [
                'label' => 'Медиа үүсгэх',
            ],
            'detach' => [
                'label' => 'Салгах',
            ],
            'view' => [
                'label' => 'Үзэх',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Нэр',
            ],
            'media' => [
                'label' => 'Зураг',
            ],
            'primary' => [
                'label' => 'Үндсэн',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Зураг',
            ],
            'file' => [
                'label' => 'Файл',
            ],
            'name' => [
                'label' => 'Нэр',
            ],
            'primary' => [
                'label' => 'Үндсэн',
            ],
        ],
        'all_media_attached' => 'Холбох бүтээгдэхүүний зураг байхгүй байна',
        'variant_description' => 'Энэ вариантод бүтээгдэхүүний зурагыг холбоно уу',
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URL-ууд',
        'actions' => [
            'create' => [
                'label' => 'URL үүсгэх',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Хэл',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Өгөгдмөл',
            ],
            'language' => [
                'label' => 'Хэл',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Өгөгдмөл',
            ],
            'language' => [
                'label' => 'Хэл',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Харилцагчийн бүлэг үнэ',
        'title_plural' => 'Харилцагчийн бүлэг үнэ',
        'table' => [
            'heading' => 'Харилцагчийн бүлэг үнэ',
            'description' => 'Бүтээгдэхүүний үнийг тодорхойлохын тулд харилцагчийн бүлэгт үнэ холбоно уу.',
            'empty_state' => [
                'label' => 'Харилцагчийн бүлэг үнэ байхгүй байна.',
                'description' => 'Харилцагчийн бүлэг үнэ үүсгэж эхлэх.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Харилцагчийн бүлэг үнэ нэмэх',
                    'modal' => [
                        'heading' => 'Харилцагчийн бүлэг үнэ үүсгэх',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Үнэ',
        'title_plural' => 'Үнэ',
        'tab_name' => 'Үнийн шатлал',
        'table' => [
            'heading' => 'Үнийн шатлал',
            'description' => 'Харилцагч их хэмжээгээр худалдаж авах үед үнийг бууруулна.',
            'empty_state' => [
                'label' => 'Үнийн шатлал байхгүй байна.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Үнийн шатлал нэмэх',
                ],
            ],
            'price' => [
                'label' => 'Үнэ',
            ],
            'customer_group' => [
                'label' => 'Харилцагчийн бүлэг',
                'placeholder' => 'Бүх харилцагчийн бүлгүүд',
            ],
            'min_quantity' => [
                'label' => 'Хамгийн бага тоо',
            ],
            'currency' => [
                'label' => 'Валют',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Үнэ',
                'helper_text' => 'Хөнгөлөлтөөс өмнөх худалдалын үнэ.',
            ],
            'customer_group_id' => [
                'label' => 'Харилцагчийн бүлэг',
                'placeholder' => 'Бүх харилцагчийн бүлгүүд',
                'helper_text' => 'Энэ үнийг хэрэглэх харилцагчийн бүлгийг сонгоно уу.',
            ],
            'min_quantity' => [
                'label' => 'Хамгийн бага тоо',
                'helper_text' => 'Энэ үнэ боломжтой болох хамгийн бага тоог сонгоно уу.',
                'validation' => [
                    'unique' => 'Харилцагчийн бүлэг болон хамгийн бага тоо өөр байх ёстой.',
                ],
            ],
            'currency_id' => [
                'label' => 'Валют',
                'helper_text' => 'Энэ үнийн валютыг сонгоно уу.',
            ],
            'compare_price' => [
                'label' => 'Харьцуулах үнэ',
                'helper_text' => 'Эхний үнэ эсвэл худалдалын үнийг харьцуулах зориулалттай.',
            ],
            'basePrices' => [
                'title' => 'Үнүүд',
                'form' => [
                    'price' => [
                        'label' => 'Үнэ',
                        'helper_text' => 'Хөнгөлөлтөөс өмнөх худалдалын үнэ.',
                        'sync_price' => 'Үнэ анхны валюттай синхрончлогдсон.',
                    ],
                    'compare_price' => [
                        'label' => 'Харьцуулах үнэ',
                        'helper_text' => 'Эхний үнэ эсвэл худалдалын үнийг харьцуулах зориулалттай.',
                    ],
                ],
                'tooltip' => 'Валютын ханшаар автоматаар үүсгэгдсэн.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Хувь',
            ],
            'tax_class' => [
                'label' => 'Татварын ангилал',
            ],
        ],
    ],
    'values' => [
        'title' => 'Утгууд',
        'form' => [
            'name' => [
                'label' => 'Нэр',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Нэр',
            ],
            'position' => [
                'label' => 'Байрлал',
            ],
        ],
    ],

];
