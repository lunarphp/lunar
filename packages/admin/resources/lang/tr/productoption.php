<?php

return [

    'label' => 'Ürün Seçeneği',

    'plural_label' => 'Ürün Seçenekleri',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'label' => [
            'label' => 'Etiket',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'shared' => [
            'label' => 'Paylaşılan',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'label' => [
            'label' => 'Etiket',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Ürün Varyantları Kaydedildi',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'İptal',
                ],
                'save-options' => [
                    'label' => 'Seçenekleri Kaydet',
                ],
                'add-shared-option' => [
                    'label' => 'Paylaşılan Seçenek Ekle',
                    'form' => [
                        'product_option' => [
                            'label' => 'Ürün Seçeneği',
                        ],
                        'no_shared_components' => [
                            'label' => 'Mevcut paylaşılan seçenek yok.',
                        ],
                        'preselect' => [
                            'label' => 'Varsayılan olarak tüm değerleri önceden seç.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Seçenek Ekle',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Yapılandırılmış ürün seçeneği yok',
                    'description' => 'Varyant oluşturmaya başlamak için paylaşılan veya kısıtlanmış ürün seçeneği ekleyin.',
                ],
            ],
            'options-table' => [
                'title' => 'Ürün Seçenekleri',
                'configure-options' => [
                    'label' => 'Seçenekleri Yapılandır',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Seçenek',
                    ],
                    'values' => [
                        'label' => 'Değerler',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Ürün Varyantları',
                'actions' => [
                    'create' => [
                        'label' => 'Varyant Oluştur',
                    ],
                    'edit' => [
                        'label' => 'Düzenle',
                    ],
                    'delete' => [
                        'label' => 'Sil',
                    ],
                ],
                'empty' => [
                    'heading' => 'Yapılandırılmış Varyant Yok',
                ],
                'table' => [
                    'new' => [
                        'label' => 'YENİ',
                    ],
                    'option' => [
                        'label' => 'Seçenek',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Fiyat',
                    ],
                    'stock' => [
                        'label' => 'Stok',
                    ],
                ],
            ],
        ],
    ],

];
