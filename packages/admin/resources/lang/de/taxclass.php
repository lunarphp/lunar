<?php

return [

    'label' => 'Steuerklasse',

    'plural_label' => 'Steuerklassen',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'default' => [
            'label' => 'Standard',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'default' => [
            'label' => 'Standard',
        ],
    ],

    'delete' => [
        'error' => [
            'title' => 'Steuerklasse kann nicht gelöscht werden',
            'body' => 'Diese Steuerklasse hat zugeordnete Produktvarianten und kann nicht gelöscht werden.',
        ],
    ],

];
