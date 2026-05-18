<?php

return [

    'label' => 'Бүтээгдэхүүний төрөл',

    'plural_label' => 'Бүтээгдэхүүний төрлүүд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'products_count' => [
            'label' => 'Бүтээгдэхүүний тоо',
        ],
        'product_attributes_count' => [
            'label' => 'Бүтээгдэхүүний атрибутууд',
        ],
        'variant_attributes_count' => [
            'label' => 'Вариантын атрибутууд',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Бүтээгдэхүүний атрибутууд',
        ],
        'variant_attributes' => [
            'label' => 'Вариантын атрибутууд',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Атрибутын бүлгүүд байхгүй байна.',
        'no_attributes' => 'Атрибутууд байхгүй байна.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Энэ бүтээгдэхүүний төрөлтэй бүтээгдэхүүнүүд холбогдсон тул устгах боломжгүй байна.',
            ],
        ],
    ],

];
