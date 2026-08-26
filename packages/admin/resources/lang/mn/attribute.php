<?php

return [

    'label' => 'Атрибут',

    'plural_label' => 'Атрибутууд',

    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'description' => [
            'label' => 'Тайлбар',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'type' => [
            'label' => 'Төрөл',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Төрөл',
        ],
        'name' => [
            'label' => 'Нэр',
        ],
        'description' => [
            'label' => 'Тайлбар',
            'helper' => 'Туслах текст харуулахад ашиглана',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'searchable' => [
            'label' => 'Хайлт хийх боломжтой',
        ],
        'filterable' => [
            'label' => 'Шүүлт хийх боломжтой',
        ],
        'required' => [
            'label' => 'Шаардлагатай',
        ],
        'type' => [
            'label' => 'Төрөл',
        ],
        'validation_rules' => [
            'label' => 'Баталгаажуулах дүрэм',
            'helper' => 'Атрибутын талбарын дүрэм, жишээ: min:1|max:10|...',
        ],
        'default_value' => [
            'label' => 'Өгөгдмөл утга',
            'helper' => 'Энэ атрибуттай шинэ бичлэг үүсгэхэд анхны утга болгон хэрэглэгдэнэ.',
        ],
    ],
];
