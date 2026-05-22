<?php

return [

    'label' => 'Customer Group',

    'plural_label' => 'Customer Groups',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'default' => [
            'label' => 'Default',
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
            'label' => 'Default',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'This customer group can not be deleted as there are customers associated.',
            ],
        ],
    ],
];
