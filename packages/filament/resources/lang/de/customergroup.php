<?php

return [

    'label' => 'Kundengruppe',

    'plural_label' => 'Kundengruppen',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'default' => [
            'label' => 'Standard',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'default' => [
            'label' => 'Standard',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Diese Kundengruppe kann nicht gelöscht werden, da damit verbundene Kunden vorhanden sind.',
            ],
        ],
    ],
];
