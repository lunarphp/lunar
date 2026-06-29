<?php

return [

    'label' => 'Region',

    'plural_label' => 'Regiony',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'default' => [
            'label' => 'Domyślny',
        ],
        'channel' => [
            'label' => 'Kanał',
        ],
        'currency' => [
            'label' => 'Waluta',
        ],
        'language' => [
            'label' => 'Język',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'channel' => [
            'label' => 'Kanał',
        ],
        'currency' => [
            'label' => 'Waluta',
        ],
        'language' => [
            'label' => 'Język',
        ],
        'tax_zone' => [
            'label' => 'Strefa podatkowa wyświetlania',
        ],
        'prices_inc_tax' => [
            'label' => 'Wyświetlanie cen',
            'options' => [
                'inherit' => 'Użyj domyślnej wartości sklepu',
                'inclusive' => 'Z podatkiem',
                'exclusive' => 'Bez podatku',
            ],
        ],
        'countries' => [
            'label' => 'Kraje',
        ],
        'default' => [
            'label' => 'Domyślny',
        ],
    ],

];
