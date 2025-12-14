<?php

return [

    'label' => 'Ürün Türü',

    'plural_label' => 'Ürün Türleri',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'products_count' => [
            'label' => 'Ürün sayısı',
        ],
        'product_attributes_count' => [
            'label' => 'Ürün Özellikleri',
        ],
        'variant_attributes_count' => [
            'label' => 'Varyant Özellikleri',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Ürün Özellikleri',
        ],
        'variant_attributes' => [
            'label' => 'Varyant Özellikleri',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Mevcut özellik grubu yok.',
        'no_attributes' => 'Mevcut özellik yok.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Bu ürün türü silinemez çünkü ilişkili ürünler var.',
            ],
        ],
    ],

];
