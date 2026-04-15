<?php

return [

    'label' => 'Belastingklasse',

    'plural_label' => 'Belastingklassen',

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'default' => [
            'label' => 'Standaard',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'default' => [
            'label' => 'Standaard',
        ],
    ],

    'delete' => [
        'error' => [
            'title' => 'Kan belastingklasse niet verwijderen',
            'body' => 'Deze belastingklasse heeft gekoppelde productvarianten en kan niet worden verwijderd.',
        ],
    ],

];
