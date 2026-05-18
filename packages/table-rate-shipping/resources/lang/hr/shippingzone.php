<?php

return [
    'label' => 'Zona dostave',
    'label_plural' => 'Zone dostave',
    'form' => [
        'unrestricted' => [
            'content' => 'Ova zona dostave nema postavljena ograničenja i bit će dostupna svim kupcima prilikom naplate.',
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'type' => [
            'label' => 'Tip',
            'options' => [
                'unrestricted' => 'Bez ograničenja',
                'countries' => 'Ograniči na države',
                'states' => 'Ograniči na pokrajine / regije',
                'postcodes' => 'Ograniči na poštanske brojeve',
            ],
        ],
        'country' => [
            'label' => 'Država',
        ],
        'states' => [
            'label' => 'Pokrajine',
        ],
        'countries' => [
            'label' => 'Države',
        ],
        'postcodes' => [
            'label' => 'Poštanski brojevi',
            'helper' => 'Navedite svaki poštanski broj u novom retku. Podržani su zamjenski znakovi poput NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'type' => [
            'label' => 'Tip',
            'options' => [
                'unrestricted' => 'Bez ograničenja',
                'countries' => 'Ograniči na države',
                'states' => 'Ograniči na pokrajine / regije',
                'postcodes' => 'Ograniči na poštanske brojeve',
            ],
        ],
    ],
];
