<?php

return [

    'label' => 'Klasa podatkowa',

    'plural_label' => 'Klasy podatkowe',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'default' => [
            'label' => 'Domyślna',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'default' => [
            'label' => 'Domyślna',
        ],
    ],

    'delete' => [
        'error' => [
            'title' => 'Nie można usunąć klasy podatkowej',
            'body' => 'Ta klasa podatkowa ma powiązane warianty produktów i nie może zostać usunięta.',
        ],
    ],

];
