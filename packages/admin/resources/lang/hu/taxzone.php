<?php

return [

    'label' => 'Adózóna',

    'plural_label' => 'Adózónák',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'zone_type' => [
            'label' => 'Zóna típusa',
        ],
        'active' => [
            'label' => 'Aktív',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'zone_type' => [
            'label' => 'Zóna típusa',
            'options' => [
                'country' => 'Csak országokra korlátozva',
                'states' => 'Csak államokra korlátozva',
                'postcodes' => 'Csak irányítószámokra korlátozva',
            ],
        ],
        'price_display' => [
            'label' => 'Ár megjelenítése',
            'options' => [
                'include_tax' => 'Adót is tartalmaz',
                'exclude_tax' => 'Adót nem tartalmaz',
            ],
        ],
        'active' => [
            'label' => 'Aktív',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],

        'zone_countries' => [
            'label' => 'Országok',
        ],

        'zone_country' => [
            'label' => 'Ország',
        ],

        'zone_states' => [
            'label' => 'Államok',
        ],

        'zone_postcodes' => [
            'label' => 'Irányítószámok',
            'helper' => 'Listázd az egyes irányítószámokat új sorban. Támogatja a helyettesítő karaktereket, mint például NW*',
        ],

    ],

];
