<?php

return [

    'label' => 'Collection',

    'plural_label' => 'Collections',

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Child Collections',
            'actions' => [
                'create_child' => [
                    'label' => 'Create Child Collection',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'No. Children',
                ],
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Basic Information',
        ],
        'products' => [
            'label' => 'Products',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Product',
                ],
            ],
        ],
    ],

];
