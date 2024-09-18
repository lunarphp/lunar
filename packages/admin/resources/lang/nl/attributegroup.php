<?php

return [

    'label' => 'Attribute Group',

    'plural_label' => 'Attribute Groups',

    'table' => [
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Position',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Position',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'This attribute group can not be deleted as there are attributes associated.',
            ],
        ],
    ],
];
