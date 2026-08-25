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
        'group' => [
            'label' => 'Бүлэг',
            'ungrouped' => 'Бүлэггүй',
        ],
    ],
    'form' => [
        'attribute_group' => [
            'label' => 'Бүлэг',
            'placeholder' => 'Бүлэггүй',
        ],
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
            'helper' => 'Мөр бүрд нэг дүрэм, жишээ нь: min:1, max:10',
        ],
    ],

    'actions' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Системийн атрибутыг устгах боломжгүй.',
            ],
        ],
    ],
];
