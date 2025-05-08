<?php

return [

    'label' => 'Colección',

    'plural_label' => 'Colecciones',

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Colecciones Hijas',
            'actions' => [
                'create_child' => [
                    'label' => 'Crear Colección Hija',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'N.º Hijas',
                ],
                'name' => [
                    'label' => 'Nombre',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Información Básica',
        ],
        'products' => [
            'label' => 'Productos',
            'actions' => [
                'attach' => [
                    'label' => 'Asociar Producto',
                ],
            ],
        ],
    ],

];
