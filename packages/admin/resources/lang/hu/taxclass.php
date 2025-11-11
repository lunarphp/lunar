<?php

return [

    'label' => 'Adóosztály',

    'plural_label' => 'Adóosztályok',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'default' => [
            'label' => 'Alapértelmezett',
        ],
    ],
    'delete' => [
        'error' => [
            'title' => 'Nem törölhető az adóosztály',
            'body' => 'Ennek az adóosztálynak vannak hozzá rendelt termékváltozatai, ezért nem törölhető.',
        ],
    ],
];
