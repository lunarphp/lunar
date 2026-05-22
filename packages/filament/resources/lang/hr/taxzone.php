<?php

return [

    'label' => 'Porezna zona',

    'plural_label' => 'Porezne zone',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'zone_type' => [
            'label' => 'Tip zone',
        ],
        'active' => [
            'label' => 'Aktivno',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'zone_type' => [
            'label' => 'Tip zone',
            'options' => [
                'country' => 'Ograniči na države',
                'states' => 'Ograniči na županije / savezne države',
                'postcodes' => 'Ograniči na poštanske brojeve',
            ],
        ],
        'price_display' => [
            'label' => 'Prikaz cijene',
            'options' => [
                'include_tax' => 'Uključi porez',
                'exclude_tax' => 'Isključi porez',
            ],
        ],
        'active' => [
            'label' => 'Aktivno',
        ],
        'default' => [
            'label' => 'Zadano',
        ],

        'zone_countries' => [
            'label' => 'Države',
        ],

        'zone_country' => [
            'label' => 'Država',
        ],

        'zone_states' => [
            'label' => 'Županije / Savezne države',
        ],

        'zone_postcodes' => [
            'label' => 'Poštanski brojevi',
            'helper' => 'Navedite svaki poštanski broj u novom retku. Podržava zamjenske znakove poput NW*',
        ],

    ],

];
