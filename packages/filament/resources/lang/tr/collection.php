<?php

return [
    'label' => 'Koleksiyon',
    'plural_label' => 'Koleksiyonlar',
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
    ],
    'pages' => [
        'children' => [
            'label' => 'Alt Koleksiyonlar',
            'actions' => [
                'create_child' => [
                    'label' => 'Alt Koleksiyon Oluştur',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Alt Koleksiyon Sayısı',
                ],
                'name' => [
                    'label' => 'Ad',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Temel Bilgiler',
        ],
        'products' => [
            'label' => 'Ürünler',
            'actions' => [
                'attach' => [
                    'label' => 'Ürün Ekle',
                ],
            ],
        ],
    ],
];
