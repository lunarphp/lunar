<?php

return [

    'label' => 'Grupo de Atributos',

    'plural_label' => 'Grupos de Atributos',

    'table' => [
        'attributable_type' => [
            'label' => 'Tipo',
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'position' => [
            'label' => 'Posición',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Tipo',
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'position' => [
            'label' => 'Posición',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este grupo de atributos no puede ser eliminado porque tiene atributos asociados.',
            ],
        ],
    ],
];
