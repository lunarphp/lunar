<?php

return [

    'label' => 'Regiune',

    'plural_label' => 'Regiuni',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'default' => [
            'label' => 'Implicit',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'currency' => [
            'label' => 'Monedă',
        ],
        'language' => [
            'label' => 'Limbă',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'currency' => [
            'label' => 'Monedă',
        ],
        'language' => [
            'label' => 'Limbă',
        ],
        'tax_zone' => [
            'label' => 'Zonă de taxe pentru afișare',
        ],
        'prices_inc_tax' => [
            'label' => 'Afișarea prețurilor',
            'options' => [
                'inherit' => 'Folosește valoarea implicită a magazinului',
                'inclusive' => 'Cu taxe incluse',
                'exclusive' => 'Fără taxe incluse',
            ],
        ],
        'countries' => [
            'label' => 'Țări',
        ],
        'default' => [
            'label' => 'Implicit',
        ],
    ],

];
