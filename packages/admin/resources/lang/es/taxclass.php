<?php

return [

    'label' => 'Clase de Impuesto',

    'plural_label' => 'Clases de Impuesto',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

    'delete' => [
        'error' => [
            'title' => 'No se puede eliminar la clase de impuesto',
            'body' => 'Esta clase de impuesto tiene variantes de producto asociadas y no se puede eliminar.',
        ],
    ],

];
