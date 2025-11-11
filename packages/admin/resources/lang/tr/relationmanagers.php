<?php

return [
    'customer_groups' => [
        'title' => 'Müşteri Grupları',
        'actions' => [
            'attach' => [
                'label' => 'Müşteri Grubu Ekle',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Ad',
            ],
            'enabled' => [
                'label' => 'Etkin',
            ],
            'starts_at' => [
                'label' => 'Başlangıç Tarihi',
            ],
            'ends_at' => [
                'label' => 'Bitiş Tarihi',
            ],
            'visible' => [
                'label' => 'Görünür',
            ],
            'purchasable' => [
                'label' => 'Satın Alınabilir',
            ],
        ],
        'table' => [
            'description' => 'Müşteri gruplarını bu :type ile ilişkilendirerek kullanılabilirliğini belirleyin.',
            'name' => [
                'label' => 'Ad',
            ],
            'enabled' => [
                'label' => 'Etkin',
            ],
            'starts_at' => [
                'label' => 'Başlangıç Tarihi',
            ],
            'ends_at' => [
                'label' => 'Bitiş Tarihi',
            ],
            'visible' => [
                'label' => 'Görünür',
            ],
            'purchasable' => [
                'label' => 'Satın Alınabilir',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Kanallar',
        'actions' => [
            'attach' => [
                'label' => 'Başka Bir Kanal Zamanla',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Etkin',
                'helper_text_false' => 'Bu kanal, başlangıç tarihi mevcut olsa bile etkinleştirilmeyecek.',
            ],
            'starts_at' => [
                'label' => 'Başlangıç Tarihi',
                'helper_text' => 'Herhangi bir tarihten itibaren kullanılabilir olması için boş bırakın.',
            ],
            'ends_at' => [
                'label' => 'Bitiş Tarihi',
                'helper_text' => 'Süresiz olarak kullanılabilir olması için boş bırakın.',
            ],
        ],
        'table' => [
            'description' => 'Hangi kanalların etkin olduğunu belirleyin ve kullanılabilirliği zamanlayin.',
            'name' => [
                'label' => 'Ad',
            ],
            'enabled' => [
                'label' => 'Etkin',
            ],
            'starts_at' => [
                'label' => 'Başlangıç Tarihi',
            ],
            'ends_at' => [
                'label' => 'Bitiş Tarihi',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Medya',
        'title_plural' => 'Medya',
        'actions' => [
            'attach' => [
                'label' => 'Medya Ekle',
            ],
            'create' => [
                'label' => 'Medya Oluştur',
            ],
            'detach' => [
                'label' => 'Ayır',
            ],
            'view' => [
                'label' => 'Görüntüle',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Ad',
            ],
            'media' => [
                'label' => 'Görsel',
            ],
            'primary' => [
                'label' => 'Ana',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Görsel',
            ],
            'file' => [
                'label' => 'Dosya',
            ],
            'name' => [
                'label' => 'Ad',
            ],
            'primary' => [
                'label' => 'Ana',
            ],
        ],
        'all_media_attached' => 'Eklenecek ürün görseli yok',
        'variant_description' => 'Bu varyanta ürün görsellerini ekleyin',
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URL\'ler',
        'actions' => [
            'create' => [
                'label' => 'URL Oluştur',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Dil',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Varsayılan',
            ],
            'language' => [
                'label' => 'Dil',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Varsayılan',
            ],
            'language' => [
                'label' => 'Dil',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Müşteri Grubu Fiyatlandırması',
        'title_plural' => 'Müşteri Grubu Fiyatlandırması',
        'table' => [
            'heading' => 'Müşteri Grubu Fiyatlandırması',
            'description' => 'Ürün fiyatını belirlemek için müşteri gruplarına fiyat ilişkilendirin.',
            'empty_state' => [
                'label' => 'Müşteri grubu fiyatlandırması yok.',
                'description' => 'Başlamak için bir müşteri grubu fiyatı oluşturun.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Müşteri Grubu Fiyatı Ekle',
                    'modal' => [
                        'heading' => 'Müşteri Grubu Fiyatı Oluştur',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Fiyatlandırma',
        'title_plural' => 'Fiyatlandırma',
        'tab_name' => 'Basamaklı Fiyatlandırma',
        'table' => [
            'heading' => 'Basamaklı Fiyatlandırma',
            'description' => 'Müşteri daha büyük miktarlarda satın aldığında fiyatı düşürün.',
            'empty_state' => [
                'label' => 'Basamaklı fiyatlandırma yok.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Basamaklı Fiyat Ekle',
                ],
            ],
            'price' => [
                'label' => 'Fiyat',
            ],
            'customer_group' => [
                'label' => 'Müşteri Grubu',
                'placeholder' => 'Tüm Müşteri Grupları',
            ],
            'min_quantity' => [
                'label' => 'Minimum Miktar',
            ],
            'currency' => [
                'label' => 'Para Birimi',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Fiyat',
                'helper_text' => 'İndirimlerden önce satın alma fiyatı.',
            ],
            'customer_group_id' => [
                'label' => 'Müşteri Grubu',
                'placeholder' => 'Tüm Müşteri Grupları',
                'helper_text' => 'Bu fiyatın uygulanacağı müşteri grubunu seçin.',
            ],
            'min_quantity' => [
                'label' => 'Minimum Miktar',
                'helper_text' => 'Bu fiyatın geçerli olacağı minimum miktarı seçin.',
                'validation' => [
                    'unique' => 'Müşteri Grubu ve Minimum Miktar benzersiz olmalıdır.',
                ],
            ],
            'currency_id' => [
                'label' => 'Para Birimi',
                'helper_text' => 'Bu fiyat için para birimini seçin.',
            ],
            'compare_price' => [
                'label' => 'Karşılaştırma Fiyatı',
                'helper_text' => 'Satın alma fiyatıyla karşılaştırma için orijinal fiyat veya tavsiye edilen satış fiyat (RRP).',
            ],
            'basePrices' => [
                'title' => 'Fiyatlar',
                'form' => [
                    'price' => [
                        'label' => 'Fiyat',
                        'helper_text' => 'İndirimlerden önce satın alma fiyatı.',
                        'sync_price' => 'Fiyat varsayılan para birimi ile senkronize edildi.',
                    ],
                    'compare_price' => [
                        'label' => 'Karşılaştırma Fiyatı',
                        'helper_text' => 'Satın alma fiyatıyla karşılaştırma için orijinal fiyat veya tavsiye edilen satış fiyat (RRP).',
                    ],
                ],
                'tooltip' => 'Döviz kurlarına göre otomatik olarak oluşturuldu.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Yüzde',
            ],
            'tax_class' => [
                'label' => 'Vergi Sınıfı',
            ],
        ],
    ],
    'values' => [
        'title' => 'Değerler',
        'table' => [
            'name' => [
                'label' => 'Ad',
            ],
            'position' => [
                'label' => 'Pozisyon',
            ],
        ],
    ],

];
