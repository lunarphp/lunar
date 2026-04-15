<?php

return [

    'label' => 'Merk',

    'plural_label' => 'Merken',

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'products_count' => [
            'label' => 'Aantal Producten',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Dit merk kan niet worden verwijderd omdat er producten aan zijn gekoppeld.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Basis Informatie',
        ],
        'products' => [
            'label' => 'Producten',
            'actions' => [
                'attach' => [
                    'label' => 'Koppel een product',
                    'form' => [
                        'record_id' => [
                            'label' => 'Product',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Product gekoppeld aan merk',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Product ontkoppeld.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Collecties',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Selecteer een collectie',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Koppel een collectie',
                ],
            ],
        ],
    ],

];
