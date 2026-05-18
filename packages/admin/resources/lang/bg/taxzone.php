<?php

return [

    'label' => 'Данъчна зона',

    'plural_label' => 'Данъчни зони',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'zone_type' => [
            'label' => 'Тип зона',
        ],
        'active' => [
            'label' => 'Активна',
        ],
        'default' => [
            'label' => 'По подразбиране',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'zone_type' => [
            'label' => 'Тип зона',
            'options' => [
                'country' => 'Ограничи до държави',
                'states' => 'Ограничи до щати/региони',
                'postcodes' => 'Ограничи до пощенски кодове',
            ],
        ],
        'price_display' => [
            'label' => 'Показване на цена',
            'options' => [
                'include_tax' => 'Включително данък',
                'exclude_tax' => 'Без данък',
            ],
        ],
        'active' => [
            'label' => 'Активна',
        ],
        'default' => [
            'label' => 'По подразбиране',
        ],

        'zone_countries' => [
            'label' => 'Държави',
        ],

        'zone_country' => [
            'label' => 'Държава',
        ],

        'zone_states' => [
            'label' => 'Щати/региони',
        ],

        'zone_postcodes' => [
            'label' => 'Пощенски кодове',
            'helper' => 'Изброявайте всеки пощенски код на нов ред. Поддържа заместващи символи като NW*',
        ],

    ],

];
