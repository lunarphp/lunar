<?php

return [

    'label' => 'Zonă de taxe',

    'plural_label' => 'Zone de taxe',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'zone_type' => [
            'label' => 'Tip zonă',
        ],
        'active' => [
            'label' => 'Activă',
        ],
        'default' => [
            'label' => 'Implicită',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'zone_type' => [
            'label' => 'Tip zonă',
            'options' => [
                'country' => 'Limitează la țări',
                'states' => 'Limitează la județe',
                'postcodes' => 'Limitează la coduri poștale',
            ],
        ],
        'price_display' => [
            'label' => 'Afișare preț',
            'options' => [
                'include_tax' => 'Include taxe',
                'exclude_tax' => 'Exclude taxe',
            ],
        ],
        'active' => [
            'label' => 'Activă',
        ],
        'default' => [
            'label' => 'Implicită',
        ],

        'zone_countries' => [
            'label' => 'Țări',
        ],

        'zone_country' => [
            'label' => 'Țară',
        ],

        'zone_states' => [
            'label' => 'Județe',
        ],

        'zone_postcodes' => [
            'label' => 'Coduri poștale',
            'helper' => 'Listați fiecare cod poștal pe o linie nouă. Suportă wildcard-uri precum NW*',
        ],

    ],

];
