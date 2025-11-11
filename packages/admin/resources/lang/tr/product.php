<?php

return [

    'label' => 'Ürün',

    'plural_label' => 'Ürünler',

    'tabs' => [
        'all' => 'Tümü',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Şu anda taslak durumunda olan bu ürün, tüm kanallarda ve müşteri gruplarında gizlidir.',
        ],
        'availability' => [
            'customer_groups' => 'Bu ürün şu anda tüm müşteri grupları için mevcut değil.',
            'channels' => 'Bu ürün şu anda tüm kanallar için mevcut değil.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Durum',
            'states' => [
                'deleted' => 'Silindi',
                'draft' => 'Taslak',
                'published' => 'Yayınlandı',
            ],
        ],
        'name' => [
            'label' => 'Ad',
        ],
        'brand' => [
            'label' => 'Marka',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Stok',
        ],
        'producttype' => [
            'label' => 'Ürün Türü',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Durumu Güncelle',
            'heading' => 'Durumu Güncelle',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'brand' => [
            'label' => 'Marka',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Ürün Türü',
        ],
        'status' => [
            'label' => 'Durum',
            'options' => [
                'published' => [
                    'label' => 'Yayınlandı',
                    'description' => 'Bu ürün tüm etkin müşteri grupları ve kanallarda mevcut olacak',
                ],
                'draft' => [
                    'label' => 'Taslak',
                    'description' => 'Bu ürün tüm kanallarda ve müşteri gruplarında gizlenecek',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Etiketler',
            'helper_text' => 'Etiketleri Enter, Tab veya virgül (,) tuşuna basarak ayırın',
        ],
        'collections' => [
            'label' => 'Koleksiyonlar',
            'select_collection' => 'Bir koleksiyon seçin',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Erişilebilirlik',
        ],
        'edit' => [
            'title' => 'Temel Bilgiler',
        ],
        'identifiers' => [
            'label' => 'Ürün Tanımlayıcıları',
        ],
        'inventory' => [
            'label' => 'Envanter',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Vergi Sınıfı',
                ],
                'tax_ref' => [
                    'label' => 'Vergi Referansı',
                    'helper_text' => 'İsteğe bağlı, 3. parti sistemlerle entegrasyon için.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Kargo',
        ],
        'variants' => [
            'label' => 'Varyantlar',
        ],
        'collections' => [
            'label' => 'Koleksiyonlar',
            'select_collection' => 'Bir koleksiyon seçin',
        ],
        'associations' => [
            'label' => 'Ürün İlişkileri',
        ],
    ],

];
