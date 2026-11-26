<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
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
        'continue_selling' => [
            'label' => 'Stok bittiğinde satışa devam et',
            'tooltip' => 'Açıkken bu varyant stoktan bağımsız olarak sipariş edilebilir. Kapalıyken müşteriler yalnızca mevcut olanı satın alabilir.',
        ],
        'sell_against_incoming' => [
            'label' => 'Gelen stok üzerinden sat',
            'tooltip' => 'Gelen stoku mevcut olarak sayar, böylece müşteriler beklediğiniz birimleri sipariş edebilir.',
        ],
        'unit_quantity' => [
            'label' => 'Birim Miktarı',
            'tooltip' => '1 birimi kaç adet ürün oluşturur.',
        ],
        'min_quantity' => [
            'label' => 'Minimum Miktar',
            'tooltip' => 'Tek bir satın alma işleminde satın alınabilecek minimum ürün varyantı miktarı.',
        ],
        'quantity_increment' => [
            'label' => 'Miktar Artışı',
            'tooltip' => 'Ürün varyantı bu miktarın katları halinde satın alınmalıdır.',
        ],
        'tax_class_id' => [
            'label' => 'Vergi Sınıfı',
        ],
        'shippable' => [
            'label' => 'Kargo Edilebilir',
        ],
        'fulfilment_methods' => [
            'label' => 'Karşılama yöntemleri',
            'helper_text' => 'Bu varyantın nasıl karşılandığı. Gönderim veya mağazadan teslim alma gibi fiziksel bir yöntemi işaretlemek onu gönderilebilir yapar. Anında indirme gibi karşılanacak bir şey yoksa hiçbirini işaretlemeyin.',
            'unavailable' => ':methods şu anda kullanılamıyor, bu yüzden müşteriler seçemez. İşaretini kaldırın veya yeniden kullanılabilir hale getirin.',
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
