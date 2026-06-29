<?php

return [

    'label' => 'Регион',

    'plural_label' => 'Региони',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'default' => [
            'label' => 'По подразбиране',
        ],
        'channel' => [
            'label' => 'Канал',
        ],
        'currency' => [
            'label' => 'Валута',
        ],
        'language' => [
            'label' => 'Език',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'channel' => [
            'label' => 'Канал',
        ],
        'currency' => [
            'label' => 'Валута',
        ],
        'language' => [
            'label' => 'Език',
        ],
        'tax_zone' => [
            'label' => 'Данъчна зона за показване',
        ],
        'prices_inc_tax' => [
            'label' => 'Показване на цени',
            'options' => [
                'inherit' => 'Използване на стойността по подразбиране',
                'inclusive' => 'С включен данък',
                'exclusive' => 'Без включен данък',
            ],
        ],
        'countries' => [
            'label' => 'Държави',
        ],
        'default' => [
            'label' => 'По подразбиране',
        ],
    ],

];
