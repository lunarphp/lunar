<?php

return [

    'label' => 'Grupo de Atributos',

    'plural_label' => 'Grupos de Atributos',

    'table' => [
        'attributable_type' => [
            'label' => 'Tipo',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'position' => [
            'label' => 'Posição',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Tipo',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'position' => [
            'label' => 'Posição',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este grupo de atributos não pode ser excluído pois há atributos associados.',
            ],
        ],
    ],
];
