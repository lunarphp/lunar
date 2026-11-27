<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'Ирэх',
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
        'continue_selling' => [
            'label' => 'Нөөц дууссан үед үргэлжлүүлэн зарах',
            'tooltip' => 'Асаалттай үед энэ хувилбарыг нөөцөөс үл хамааран захиалах боломжтой. Унтраалттай үед хэрэглэгчид зөвхөн байгаа нөөцийг худалдан авна.',
        ],
        'sell_against_incoming' => [
            'label' => 'Ирэх нөөцөөр зарах',
            'tooltip' => 'Ирэх нөөцийг боломжтойд тооцож, хэрэглэгчид хүлээгдэж буй нэгжүүдийг захиалах боломжтой болно.',
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
        'fulfilment_methods' => [
            'label' => 'Биелүүлэлтийн аргууд',
            'helper_text' => 'Энэ хувилбарыг хэрхэн биелүүлэх. Хүргэлт эсвэл очиж авах зэрэг биет аргыг сонговол хүргэх боломжтой болно. Шууд татаж авах гэх мэт биелүүлэх зүйлгүй бол юу ч бүү сонго.',
            'unavailable' => ':methods одоогоор боломжгүй тул үйлчлүүлэгчид сонгох боломжгүй. Сонголтыг арилгах эсвэл дахин боломжтой болгоно уу.',
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
