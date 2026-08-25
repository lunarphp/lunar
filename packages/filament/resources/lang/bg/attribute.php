<?php

return [
    'label' => 'Атрибут',
    'plural_label' => 'Атрибути',
    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'description' => [
            'label' => 'Описание',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'type' => [
            'label' => 'Тип',
        ],
    ],
    'form' => [
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'Тип',
        ],
        'name' => [
            'label' => 'Име',
        ],
        'description' => [
            'label' => 'Описание',
            'helper' => 'Използва се за показване на помощен текст под полето',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'searchable' => [
            'label' => 'Търсим',
        ],
        'filterable' => [
            'label' => 'Филтрируем',
        ],
        'required' => [
            'label' => 'Задължително',
        ],
        'type' => [
            'label' => 'Тип',
        ],
        'validation_rules' => [
            'label' => 'Правила за валидация',
            'helper' => 'По едно правило на запис, например: min:1, max:10',
        ],
    ],
];
