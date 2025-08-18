<?php

return [

    'label' => 'Marca',

    'plural_label' => 'Marcas',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'products_count' => [
            'label' => 'N.º de productos',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Esta marca no puede ser eliminada porque tiene productos asociados.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Información básica',
        ],
        'products' => [
            'label' => 'Productos',
            'actions' => [
                'attach' => [
                    'label' => 'Asociar un producto',
                    'form' => [
                        'record_id' => [
                            'label' => 'Producto',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Producto asociado a la marca',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Producto desasociado.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Colecciones',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Seleccionar una colección',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Asociar una colección',
                ],
            ],
        ],
    ],

];
