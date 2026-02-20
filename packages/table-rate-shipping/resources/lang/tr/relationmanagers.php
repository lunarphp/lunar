<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Müşteri gruplarını bu kargo yöntemiyle ilişkilendirerek kullanılabilirliğini belirleyin.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Kargo Tarifeleri',
        'actions' => [
            'create' => [
                'label' => 'Kargo Tarifesi Oluştur',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Tüm fiyatlar vergi dahildir, minimum harcama hesaplanırken dikkate alınacaktır.',
            'prices_excl_tax' => 'Tüm fiyatlar vergi hariçtir, minimum harcama sepet ara toplamına göre olacaktır.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Kargo Yöntemi',
            ],
            'price' => [
                'label' => 'Fiyat',
            ],
            'prices' => [
                'label' => 'Fiyat Aralıkları',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Müşteri Grubu',
                        'placeholder' => 'Herhangi',
                    ],
                    'currency_id' => [
                        'label' => 'Para Birimi',
                    ],
                    'min_spend' => [
                        'label' => 'Min. Harcama',
                    ],
                    'min_weight' => [
                        'label' => 'Min. Ağırlık',
                    ],
                    'price' => [
                        'label' => 'Fiyat',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Kargo Yöntemi',
            ],
            'price' => [
                'label' => 'Fiyat',
            ],
            'price_breaks_count' => [
                'label' => 'Fiyat Aralıkları',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Kargo İstisnaları',
        'form' => [
            'purchasable' => [
                'label' => 'Ürün',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Kargo istisna listesi ekle',
            ],
            'attach' => [
                'label' => 'İstisna listesi ekle',
            ],
            'detach' => [
                'label' => 'Kaldır',
            ],
        ],
    ],
];
