<?php

return [

    'label' => 'Klantengroep',

    'plural_label' => 'Klantengroepen',

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handvat',
        ],
        'default' => [
            'label' => 'Standaard',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handvat',
        ],
        'default' => [
            'label' => 'Standaard',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Deze klantengroep kan niet worden verwijderd omdat er klanten aan zijn gekoppeld.',
            ],
        ],
    ],
];
