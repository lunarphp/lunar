<?php

return [
    'label' => 'Verzendzone',
    'label_plural' => 'Verzendzones',
    'form' => [
        'unrestricted' => [
            'content' => 'Deze verzendzone heeft geen beperkingen en is bij het afrekenen beschikbaar voor alle klanten.',
        ],
        'name' => [
            'label' => 'Naam',
        ],
        'type' => [
            'label' => 'Type',
            'options' => [
                'unrestricted' => 'Onbeperkt',
                'countries' => 'Beperk tot Landen',
                'states' => 'Beperk tot Staten / Provincies',
                'postcodes' => 'Beperk tot Postcodes',
            ],
        ],
        'country' => [
            'label' => 'Land',
        ],
        'states' => [
            'label' => 'Staten',
        ],
        'countries' => [
            'label' => 'Landen',
        ],
        'postcodes' => [
            'label' => 'Postcodes',
            'helper' => 'Plaats elke postcode op een nieuwe regel. Ondersteunt wildcards zoals NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'type' => [
            'label' => 'Type',
            'options' => [
                'unrestricted' => 'Onbeperkt',
                'countries' => 'Beperk tot Landen',
                'states' => 'Beperk tot Staten / Provincies',
                'postcodes' => 'Beperk tot Postcodes',
            ],
        ],
    ],
];
