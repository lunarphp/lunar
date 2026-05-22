<?php

return [

    'label' => 'Grup de atribute',

    'plural_label' => 'Grupe de atribute',

    'table' => [
        'attributable_type' => [
            'label' => 'Tip',
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'position' => [
            'label' => 'Poziție',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Tip',
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'position' => [
            'label' => 'Poziție',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Acest grup de atribute nu poate fi șters deoarece are atribute asociate.',
            ],
        ],
    ],
];
