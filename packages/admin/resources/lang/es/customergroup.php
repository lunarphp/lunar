<?php

return [

    'label' => 'Grupo de Clientes',

    'plural_label' => 'Grupos de Clientes',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este grupo de clientes no puede ser eliminado ya que hay clientes asociados.',
            ],
        ],
    ],
];
