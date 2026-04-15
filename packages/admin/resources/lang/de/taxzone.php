<?php

return [

    'label' => 'Steuerzone',

    'plural_label' => 'Steuerzonen',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'zone_type' => [
            'label' => 'Zonentyp',
        ],
        'active' => [
            'label' => 'Aktiv',
        ],
        'default' => [
            'label' => 'Standard',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'zone_type' => [
            'label' => 'Zonentyp',
            'options' => [
                'country' => 'Auf Länder beschränken',
                'states' => 'Auf Staaten beschränken',
                'postcodes' => 'Auf Postleitzahlen beschränken',
            ],
        ],
        'price_display' => [
            'label' => 'Preis anzeigen',
            'options' => [
                'include_tax' => 'Steuer einschließen',
                'exclude_tax' => 'Steuer ausschließen',
            ],
        ],
        'active' => [
            'label' => 'Aktiv',
        ],
        'default' => [
            'label' => 'Standard',
        ],

        'zone_countries' => [
            'label' => 'Länder',
        ],

        'zone_country' => [
            'label' => 'Land',
        ],

        'zone_states' => [
            'label' => 'Staaten',
        ],

        'zone_postcodes' => [
            'label' => 'Postleitzahlen',
            'helper' => 'Listen Sie jede Postleitzahl in einer neuen Zeile auf. Unterstützt Platzhalter wie NW*',
        ],

    ],

];
