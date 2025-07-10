<?php

return [

    'label' => 'Tax Class',

    'plural_label' => 'Tax Classes',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],
    'delete' => [
        'error' => [
            'title' => 'Cannot delete tax class',
            'body' => 'This tax class has associated product variants and cannot be deleted.',
        ],
    ],
];
