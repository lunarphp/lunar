<?php

return [

    'label' => 'Marka',

    'plural_label' => 'Markalar',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'products_count' => [
            'label' => 'Ürün Sayısı',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Bu marka silinemez çünkü ilişkili ürünler var.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Temel Bilgiler',
        ],
        'products' => [
            'label' => 'Ürünler',
            'actions' => [
                'attach' => [
                    'label' => 'Ürün ilişkilendir',
                    'form' => [
                        'record_id' => [
                            'label' => 'Ürün',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Ürün markaya eklendi',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Ürün ilişkisi kaldırıldı.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Koleksiyonlar',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Bir koleksiyon seçin',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Koleksiyon ilişkilendir',
                ],
            ],
        ],
    ],

];
