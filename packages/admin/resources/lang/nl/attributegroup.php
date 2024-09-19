<?php

return [

    'label' => 'Attribuutgroep',

    'plural_label' => 'Attribuutgroepen',

    'table' => [
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handvat',
        ],
        'position' => [
            'label' => 'Positie',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handvat',
        ],
        'position' => [
            'label' => 'Positie',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Deze attribuutgroep kan niet worden verwijderd omdat er attributen aan zijn gekoppeld.',
            ],
        ],
    ],
];
