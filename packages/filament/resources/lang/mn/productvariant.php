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
    'label' => 'Бүтээгдэхүүний вариант',
    'plural_label' => 'Бүтээгдэхүүний вариантүүд',
    'pages' => [
        'edit' => [
            'title' => 'Үндсэн мэдээлэл',
        ],
        'media' => [
            'title' => 'Медиа',
            'form' => [
                'no_selection' => [
                    'label' => 'Одоогоор энэ вариант зориулагдсан зураг сонгогдоогүй байна.',
                ],
                'no_media_available' => [
                    'label' => 'Одоогоор энэ бүтээгдэхүүнд медиа байхгүй байна.',
                ],
                'images' => [
                    'label' => 'Үндсэн зураг',
                    'helper_text' => 'Энэ вариантыг төлөөлөх бүтээгдэхүүний зурагыг сонгоно уу.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Таниулагчид',
        ],
        'inventory' => [
            'title' => 'Инвентарь',
        ],
        'shipping' => [
            'title' => 'Хүргэлт',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Дэлхийн худалдааны нэгжийн дугаар (GTIN)',
        ],
        'mpn' => [
            'label' => 'Үйлдвэрлэгчийн дугаар (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'Захиалгаар',
        ],
        'selling_policy' => [
            'label' => 'Худалдааны бодлого',
            'tooltip' => 'Энэ хувилбарыг хэзээ худалдан авах боломжтой. "Нөөцтэй үед" нь зөвхөн бараа байгаа үед зарна; "Нөөцтэй үед эсвэл захиалгаар" нь захиалгын хязгаарыг мөн зарна; "Үргэлж" нь нөөцийг огт тооцохгүй.',
            'options' => [
                'always' => 'Үргэлж',
                'in_stock' => 'Нөөцтэй үед',
                'in_stock_or_on_backorder' => 'Нөөцтэй үед эсвэл захиалгаар',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Нэгжийн тоо',
            'tooltip' => '1 нэгжид хэдэн ширхэг бараа байх вэ.',
        ],
        'min_quantity' => [
            'label' => 'Хамгийн бага тоо',
            'tooltip' => 'Нэг худалдаалалтанд худалдаж авах бүтээгдэхүүний вариантын хамгийн бага тоо.',
        ],
        'quantity_increment' => [
            'label' => 'Тооны өсөлт',
            'tooltip' => 'Бүтээгдэхүүний вариант энэ тооны давхцаагаар худалдаж авах ёстой.',
        ],
        'tax_class_id' => [
            'label' => 'Татварын ангилал',
        ],
        'shippable' => [
            'label' => 'Хүргэж болох',
        ],
        'length_value' => [
            'label' => 'Урт',
        ],
        'length_unit' => [
            'label' => 'Уртын нэгж',
        ],
        'width_value' => [
            'label' => 'Өргөн',
        ],
        'width_unit' => [
            'label' => 'Өргөний нэгж',
        ],
        'height_value' => [
            'label' => 'Өндөр',
        ],
        'height_unit' => [
            'label' => 'Өндөрийн нэгж',
        ],
        'weight_value' => [
            'label' => 'Жин',
        ],
        'weight_unit' => [
            'label' => 'Жиний нэгж',
        ],
    ],
];
