<?php

return [

    'label' => 'Brand',

    'plural_label' => 'Brands',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'products_count' => [
            'label' => 'No. Products',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'This brand can not be deleted as there are products associated.',
            ],
        ],
    ],
];
