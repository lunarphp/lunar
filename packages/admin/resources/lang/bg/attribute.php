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
            'helper' => 'Правила за поле на атрибут, пример: min:1|max:10|...',
        ],
        'default_value' => [
            'label' => 'Стойност по подразбиране',
            'helper' => 'Прилага се като начална стойност при създаване на нов запис с този атрибут.',
        ],
    ],
];
