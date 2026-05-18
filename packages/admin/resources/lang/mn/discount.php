<?php

use Lunar\Models\Discount;

return [
    'plural_label' => 'Хөнгөлөлтүүд',
    'label' => 'Хөнгөлөлт',
    'form' => [
        'conditions' => [
            'heading' => 'Нөхцөлүүд',
        ],
        'buy_x_get_y' => [
            'heading' => 'X авбал Y урамшуулалтай',
        ],
        'amount_off' => [
            'heading' => 'Хэмжээг бууруулах',
        ],
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'starts_at' => [
            'label' => 'Эхлэх огноо',
        ],
        'ends_at' => [
            'label' => 'Дуусах огноо',
        ],
        'priority' => [
            'label' => 'Эрэмбэ',
            'helper_text' => 'Илүү өндөр эрэмбэтэй хөнгөлөлтүүд эхэндээ хэрэгжинэ.',
            'options' => [
                'low' => [
                    'label' => 'Бага',
                ],
                'medium' => [
                    'label' => 'Дунд',
                ],
                'high' => [
                    'label' => 'Өндөр',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Энэ хөнгөлөлтийн дараа бусад хөнгөлөлтийг зогсоох',
        ],
        'coupon' => [
            'label' => 'Купон',
            'helper_text' => 'Хөнгөлөлт хэрэгжихийн тулд шаардлагатай купоныг оруулна уу, хоосон орхивол автоматаар хэрэгжинэ.',
        ],
        'max_uses' => [
            'label' => 'Хамгийн их ашиглалт',
            'helper_text' => 'Хязгааргүй ашиглахын тулд хоосон орхи.',
        ],
        'max_uses_per_user' => [
            'label' => 'Хэрэглэгч тутамд хамгийн их ашиглалт',
            'helper_text' => 'Хязгааргүй ашиглахын тулд хоосон орхи.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Сагсны хамгийн бага дүн',
        ],
        'min_qty' => [
            'label' => 'Бүтээгдэхүүний тоо',
            'helper_text' => 'Хөнгөлөлт хэрэгжихийн тулд шаардлагатай бүтээгдэхүүний тоог тохируулна уу.',
        ],
        'reward_qty' => [
            'label' => 'Үнэгүй барааны тоо',
            'helper_text' => 'Хөнгөлөх барааны тоо.',
        ],
        'max_reward_qty' => [
            'label' => 'Хамгийн их шагналын тоо',
            'helper_text' => 'Нөхцөлөөс үл хамааран хөнгөлөх бүтээгдэхүүний хамгийн их тоо.',
        ],
        'automatic_rewards' => [
            'label' => 'Шагналыг автоматаар нэмэх',
            'helper_text' => 'Сагсанд байхгүй бол шагнал бүтээгдэхүүн нэмэхийг идэвхжүүл.',
        ],
        'fixed_value' => [
            'label' => 'Тогтмол утга',
        ],
        'percentage' => [
            'label' => 'Хувь',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'status' => [
            'label' => 'Статус',
            Discount::ACTIVE => [
                'label' => 'Идэвхтэй',
            ],
            Discount::PENDING => [
                'label' => 'Хүлээгдэж буй',
            ],
            Discount::EXPIRED => [
                'label' => 'Хугацаа дууссан',
            ],
            Discount::SCHEDULED => [
                'label' => 'Товлогдсон',
            ],
        ],
        'type' => [
            'label' => 'Төрөл',
        ],
        'starts_at' => [
            'label' => 'Эхлэх огноо',
        ],
        'ends_at' => [
            'label' => 'Дуусах огноо',
        ],
        'created_at' => [
            'label' => 'Үүсгэсэн огноо',
        ],
        'coupon' => [
            'label' => 'Купон',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Бэлэн',
        ],
        'edit' => [
            'title' => 'Үндсэн мэдээлэл',
        ],
        'limitations' => [
            'label' => 'Хязгаарлалтууд',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Коллекцүүд',
            'description' => 'Энэ хөнгөлөлт хязгаарлах коллекцүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Коллекц холбох',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'type' => [
                    'label' => 'Төрөл',
                    'limitation' => [
                        'label' => 'Хязгаарлалт',
                    ],
                    'exclusion' => [
                        'label' => 'Хасалт',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Хязгаарлалт',
                        ],
                        'exclusion' => [
                            'label' => 'Хасалт',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Харилцагчид',
            'description' => 'Энэ хөнгөлөлт хязгаарлах харилцагчдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Харилцагч холбох',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Брендүүд',
            'description' => 'Энэ хөнгөлөлт хязгаарлах брендүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Бренд холбох',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'type' => [
                    'label' => 'Төрөл',
                    'limitation' => [
                        'label' => 'Хязгаарлалт',
                    ],
                    'exclusion' => [
                        'label' => 'Хасалт',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Хязгаарлалт',
                        ],
                        'exclusion' => [
                            'label' => 'Хасалт',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Бүтээгдэхүүнүүд',
            'description' => 'Энэ хөнгөлөлт хязгаарлах бүтээгдэхүүнүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Бүтээгдэхүүн нэмэх',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'type' => [
                    'label' => 'Төрөл',
                    'limitation' => [
                        'label' => 'Хязгаарлалт',
                    ],
                    'exclusion' => [
                        'label' => 'Хасалт',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Хязгаарлалт',
                        ],
                        'exclusion' => [
                            'label' => 'Хасалт',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Шагналууд',
            'description' => 'Дээрх нөхцөлүүд хангагдсан тохиолдолд сагсанд байвал хөнгөлөх бүтээгдэхүүнүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Шагнал нэмэх',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'type' => [
                    'label' => 'Төрөл',
                    'limitation' => [
                        'label' => 'Хязгаарлалт',
                    ],
                    'exclusion' => [
                        'label' => 'Хасалт',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Хязгаарлалт',
                        ],
                        'exclusion' => [
                            'label' => 'Хасалт',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Бүтээгдэхүүн болон Вариантын нөхцөлүүд',
            'description' => 'Хөнгөлөлт хэрэгжихийн тулд шаардлагатай бүтээгдэхүүн эсвэл вариантын нөхцөлүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Нөхцөл нэмэх',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'type' => [
                    'label' => 'Төрөл',
                    'limitation' => [
                        'label' => 'Хязгаарлалт',
                    ],
                    'exclusion' => [
                        'label' => 'Хасалт',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Хязгаарлалт',
                        ],
                        'exclusion' => [
                            'label' => 'Хасалт',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'Коллекцийн нөхцөлүүд',
            'description' => 'Хөнгөлөлт хэрэгжихийн тулд шаардлагатай коллекцийн нөхцөлүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Нөхцөл нэмэх',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Бүтээгдэхүүний вариантүүд',
            'description' => 'Энэ хөнгөлөлт хязгаарлах бүтээгдэхүүний вариантүүдийг сонгоно уу.',
            'actions' => [
                'attach' => [
                    'label' => 'Бүтээгдэхүүний вариант нэмэх',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Нэр',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Сонголт(ууд)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Хязгаарлалт',
                        ],
                        'exclusion' => [
                            'label' => 'Хасалт',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
