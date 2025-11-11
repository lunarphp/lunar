<?php

return [
    'plural_label' => 'İndirimler',
    'label' => 'İndirim',
    'form' => [
        'conditions' => [
            'heading' => 'Koşullar',
        ],
        'buy_x_get_y' => [
            'heading' => 'X Al Y Kazan',
        ],
        'amount_off' => [
            'heading' => 'Sabit Tutar İndirimi',
        ],
        'name' => [
            'label' => 'İndirim Adı',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'starts_at' => [
            'label' => 'Başlangıç Tarihi',
        ],
        'ends_at' => [
            'label' => 'Bitiş Tarihi',
        ],
        'priority' => [
            'label' => 'Öncelik',
            'helper_text' => 'Yüksek öncelikli indirimler önce uygulanır.',
            'options' => [
                'low' => [
                    'label' => 'Düşük',
                ],
                'medium' => [
                    'label' => 'Orta',
                ],
                'high' => [
                    'label' => 'Yüksek',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Bundan sonra diğer indirimlerin uygulanmasını durdur',
        ],
        'coupon' => [
            'label' => 'Kupon',
            'helper_text' => 'İndirimin uygulanması için gerekli kuponu girin, boş bırakılırsa otomatik olarak uygulanır.',
        ],
        'max_uses' => [
            'label' => 'Maksimum kullanım',
            'helper_text' => 'Sınırsız kullanım için boş bırakın.',
        ],
        'max_uses_per_user' => [
            'label' => 'Kullanıcı başına maksimum kullanım',
            'helper_text' => 'Sınırsız kullanım için boş bırakın.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Minimum Sepet Tutarı',
        ],
        'min_qty' => [
            'label' => 'Ürün Miktarı',
            'helper_text' => 'İndirimin uygulanması için gerekli ürün adedini belirleyin.',
        ],
        'reward_qty' => [
            'label' => 'Ücretsiz ürün sayısı',
            'helper_text' => 'Her üründen kaçının indirimli olacağı.',
        ],
        'max_reward_qty' => [
            'label' => 'Maksimum ödül miktarı',
            'helper_text' => 'Kriterlere bakılmaksızın indirime tabi tutulabilecek maksimum ürün miktarı.',
        ],
        'automatic_rewards' => [
            'label' => 'Ödülleri otomatik ekle',
            'helper_text' => 'Ödül ürünlerini sepette yokken eklemek için açın.',
        ],
        'fixed_value' => [
            'label' => 'Sabit değer',
        ],
        'percentage' => [
            'label' => 'Yüzde',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'status' => [
            'label' => 'Durum',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Aktif',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'Beklemede',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Süresi Dolmuş',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Planlanmış',
            ],
        ],
        'type' => [
            'label' => 'Tür',
        ],
        'starts_at' => [
            'label' => 'Başlangıç Tarihi',
        ],
        'ends_at' => [
            'label' => 'Bitiş Tarihi',
        ],
        'created_at' => [
            'label' => 'Oluşturulma Tarihi',
        ],
        'coupon' => [
            'label' => 'Kupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Erişilebilirlik',
        ],
        'edit' => [
            'title' => 'Temel Bilgiler',
        ],
        'limitations' => [
            'label' => 'Sınırlamalar',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Koleksiyonlar',
            'description' => 'Bu indirimin hangi koleksiyonlarla sınırlanacağını seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Koleksiyon Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'type' => [
                    'label' => 'Tür',
                    'limitation' => [
                        'label' => 'Sınırlama',
                    ],
                    'exclusion' => [
                        'label' => 'Hariç Tutma',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Sınırlama',
                        ],
                        'exclusion' => [
                            'label' => 'Hariç Tutma',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Müşteriler',
            'description' => 'Bu indirimin hangi müşterilerle sınırlanacağını seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Müşteri Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Markalar',
            'description' => 'Bu indirimin hangi markalarla sınırlanacağını seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Marka Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'type' => [
                    'label' => 'Tür',
                    'limitation' => [
                        'label' => 'Sınırlama',
                    ],
                    'exclusion' => [
                        'label' => 'Hariç Tutma',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Sınırlama',
                        ],
                        'exclusion' => [
                            'label' => 'Hariç Tutma',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Ürünler',
            'description' => 'Bu indirimin hangi ürünlerle sınırlanacağını seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Ürün Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'type' => [
                    'label' => 'Tür',
                    'limitation' => [
                        'label' => 'Sınırlama',
                    ],
                    'exclusion' => [
                        'label' => 'Hariç Tutma',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Sınırlama',
                        ],
                        'exclusion' => [
                            'label' => 'Hariç Tutma',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Ödüller',
            'description' => 'Sepette mevcut olmaları ve yukarıdaki koşulların karşılanması durumunda hangi ürünlerin indirimli olacağını seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Ödül Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'type' => [
                    'label' => 'Tür',
                    'limitation' => [
                        'label' => 'Sınırlama',
                    ],
                    'exclusion' => [
                        'label' => 'Hariç Tutma',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Sınırlama',
                        ],
                        'exclusion' => [
                            'label' => 'Hariç Tutma',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Koşullar',
            'description' => 'İndirimin uygulanması için gereken koşulları seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Koşul Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'type' => [
                    'label' => 'Tür',
                    'limitation' => [
                        'label' => 'Sınırlama',
                    ],
                    'exclusion' => [
                        'label' => 'Hariç Tutma',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Sınırlama',
                        ],
                        'exclusion' => [
                            'label' => 'Hariç Tutma',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Ürün Varyantları',
            'description' => 'Bu indirimin hangi ürün varyantlarıyla sınırlanacağını seçin.',
            'actions' => [
                'attach' => [
                    'label' => 'Ürün Varyantı Ekle',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Ad',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Seçenek(ler)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Sınırlama',
                        ],
                        'exclusion' => [
                            'label' => 'Hariç Tutma',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
