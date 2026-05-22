<?php

return [

    'label' => 'Grupo de Colecciones',

    'plural_label' => 'Grupos de Colecciones',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'collections_count' => [
            'label' => 'N.º Colecciones',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este grupo de colecciones no puede ser eliminado porque tiene colecciones asociadas.',
            ],
        ],
    ],
];
