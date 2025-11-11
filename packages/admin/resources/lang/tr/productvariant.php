<?php

return [
    'label' => 'Ürün Varyantı',
    'plural_label' => 'Ürün Varyantları',
    'pages' => [
        'edit' => [
            'title' => 'Temel Bilgiler',
        ],
        'media' => [
            'title' => 'Medya',
            'form' => [
                'no_selection' => [
                    'label' => 'Bu varyant için şu anda seçilmiş bir görseliniz yok.',
                ],
                'no_media_available' => [
                    'label' => 'Bu üründe şu anda mevcut medya yok.',
                ],
                'images' => [
                    'label' => 'Ana Görsel',
                    'helper_text' => 'Bu varyantı temsil eden ürün görselini seçin.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Tanımlayıcılar',
        ],
        'inventory' => [
            'title' => 'Envanter',
        ],
        'shipping' => [
            'title' => 'Kargo',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Küresel Ticaret Ürün Numarası (GTIN)',
        ],
        'mpn' => [
            'label' => 'Üretici Parça Numarası (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'label' => 'Stokta',
        ],
        'backorder' => [
            'label' => 'Sipariş Üzerine',
        ],
        'purchasable' => [
            'label' => 'Satın Alınabilirlik',
            'options' => [
                'always' => 'Her Zaman',
                'in_stock' => 'Stokta',
                'in_stock_or_on_backorder' => 'Stokta veya Sipariş Üzerine',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Birim Miktarı',
            'helper_text' => '1 birimi kaç adet ürün oluşturur.',
        ],
        'min_quantity' => [
            'label' => 'Minimum Miktar',
            'helper_text' => 'Tek bir satın alma işleminde satın alınabilecek minimum ürün varyantı miktarı.',
        ],
        'quantity_increment' => [
            'label' => 'Miktar Artışı',
            'helper_text' => 'Ürün varyantı bu miktarın katları halinde satın alınmalıdır.',
        ],
        'tax_class_id' => [
            'label' => 'Vergi Sınıfı',
        ],
        'shippable' => [
            'label' => 'Kargo Edilebilir',
        ],
        'length_value' => [
            'label' => 'Uzunluk',
        ],
        'length_unit' => [
            'label' => 'Uzunluk Birimi',
        ],
        'width_value' => [
            'label' => 'Genişlik',
        ],
        'width_unit' => [
            'label' => 'Genişlik Birimi',
        ],
        'height_value' => [
            'label' => 'Yükseklik',
        ],
        'height_unit' => [
            'label' => 'Yükseklik Birimi',
        ],
        'weight_value' => [
            'label' => 'Ağırlık',
        ],
        'weight_unit' => [
            'label' => 'Ağırlık Birimi',
        ],
    ],
];
