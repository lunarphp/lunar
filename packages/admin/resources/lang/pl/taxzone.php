<?php

return [

    'label' => 'Strefa podatkowa',

    'plural_label' => 'Strefy podatkowe',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'zone_type' => [
            'label' => 'Typ strefy',
        ],
        'active' => [
            'label' => 'Aktywna',
        ],
        'default' => [
            'label' => 'Domyślna',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'zone_type' => [
            'label' => 'Typ strefy',
            'options' => [
                'country' => 'Ogranicz do krajów',
                'states' => 'Ogranicz do stanów',
                'postcodes' => 'Ogranicz do kodów pocztowych',
            ],
        ],
        'price_display' => [
            'label' => 'Wyświetlanie ceny',
            'options' => [
                'include_tax' => 'Zawiera podatek',
                'exclude_tax' => 'Nie zawiera podatku',
            ],
        ],
        'active' => [
            'label' => 'Aktywna',
        ],
        'default' => [
            'label' => 'Domyślna',
        ],

        'zone_countries' => [
            'label' => 'Kraje',
        ],

        'zone_country' => [
            'label' => 'Kraj',
        ],

        'zone_states' => [
            'label' => 'Stany',
        ],

        'zone_postcodes' => [
            'label' => 'Kody pocztowe',
            'helper' => 'Wprowadź każdy kod pocztowy w nowej linii. Obsługuje znaki wieloznaczne, takie jak NW*',
        ],

    ],

];
