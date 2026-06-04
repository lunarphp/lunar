<?php

return [
    'label' => 'Ürün',
    'plural_label' => 'Ürünler',
    'tabs' => [
        'all' => 'Tümü',
        'published' => 'Published',
        'draft' => 'Draft',
    ],
    'status' => [
        'draft' => [
            'content' => 'Currently in draft, this product is hidden from all channels and customer groups.',
        ],
        'archived' => [
            'content' => 'This product is archived — it is hidden from the storefront, but kept on record so historical orders keep their reference. Move it back to Draft to revive it.',
        ],
        'availability' => [
            'customer_groups' => 'Bu ürün şu anda tüm müşteri grupları için mevcut değil.',
            'channels' => 'Bu ürün şu anda tüm kanallar için mevcut değil.',
            'hidden_from_guests' => 'Misafirler şu anda bu ürünü göremez veya satın alamaz. Varsayılan müşteri grubu bu ürün için etkin veya görünür değil.',
            'no_default_customer_group' => 'Varsayılan bir müşteri grubu ayarlanmadığından misafir görünürlüğü buradan kontrol edilemez. Misafir erişimini yönetmek için bir müşteri grubunu varsayılan olarak işaretleyin.',
        ],
    ],
    'table' => [
        'status' => [
            'label' => 'Durum',
            'states' => [
                'archived' => 'Archived',
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

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
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
