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
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
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
            'helper' => 'Атрибутын талбарын дүрэм, жишээ: min:1, max:10',
        ],
    ],
];
