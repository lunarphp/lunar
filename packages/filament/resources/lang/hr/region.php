<?php

return [

    'label' => 'Regija',

    'plural_label' => 'Regije',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
        'channel' => [
            'label' => 'Kanal',
        ],
        'currency' => [
            'label' => 'Valuta',
        ],
        'language' => [
            'label' => 'Jezik',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'channel' => [
            'label' => 'Kanal',
        ],
        'currency' => [
            'label' => 'Valuta',
        ],
        'language' => [
            'label' => 'Jezik',
        ],
        'tax_zone' => [
            'label' => 'Porezna zona za prikaz',
        ],
        'prices_inc_tax' => [
            'label' => 'Prikaz cijena',
            'options' => [
                'inherit' => 'Koristi zadanu vrijednost trgovine',
                'inclusive' => 'S uključenim porezom',
                'exclusive' => 'Bez poreza',
            ],
        ],
        'countries' => [
            'label' => 'Države',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
    ],

];
