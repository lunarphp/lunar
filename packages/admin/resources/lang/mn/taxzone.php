<?php

return [

    'label' => 'Татварын бүс',

    'plural_label' => 'Татварын бүсүүд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'zone_type' => [
            'label' => 'Бүсийн төрөл',
        ],
        'active' => [
            'label' => 'Идэвхтэй',
        ],
        'default' => [
            'label' => 'Өгөгдмөл',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'zone_type' => [
            'label' => 'Бүсийн төрөл',
            'options' => [
                'country' => 'Улсуудад хязгаарлах',
                'states' => 'Аймаг/Муж-д хязгаарлах',
                'postcodes' => 'Шуудангийн кодод хязгаарлах',
            ],
        ],
        'price_display' => [
            'label' => 'Үнийн харуулалт',
            'options' => [
                'include_tax' => 'Татварыг оруулан',
                'exclude_tax' => 'Татварыг оруулалгүй',
            ],
        ],
        'active' => [
            'label' => 'Идэвхтэй',
        ],
        'default' => [
            'label' => 'Өгөгдмөл',
        ],

        'zone_countries' => [
            'label' => 'Улсууд',
        ],

        'zone_country' => [
            'label' => 'Улс',
        ],

        'zone_states' => [
            'label' => 'Аймаг/Муж',
        ],

        'zone_postcodes' => [
            'label' => 'Шуудангийн код',
            'helper' => 'Шуудангийн кодыг шинэ мөрөнд бичнэ үү. NW* гэх мэт тэмдэглэгээ дэмжинэ',
        ],

    ],

];
