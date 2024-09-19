<?php

return [

    'label' => 'Collectiegroep',

    'plural_label' => 'Collectiegroepen',

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handvat',
        ],
        'collections_count' => [
            'label' => 'Aantal Collecties',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handvat',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Deze collectiegroep kan niet worden verwijderd omdat er collecties aan zijn gekoppeld.',
            ],
        ],
    ],
];
