<?php

return [

    'label' => 'Porezni razred',

    'plural_label' => 'Porezni razredi',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'default' => [
            'label' => 'Zadano',
        ],
    ],

    'delete' => [
        'error' => [
            'title' => 'Porezni razred nije moguće izbrisati',
            'body' => 'Ovaj porezni razred ima pridružene varijante proizvoda i nije ga moguće izbrisati.',
        ],
    ],

];
