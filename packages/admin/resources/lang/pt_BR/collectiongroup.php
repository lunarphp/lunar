<?php

return [

    'label' => 'Grupo de Coleções',

    'plural_label' => 'Grupos de Coleções',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'collections_count' => [
            'label' => 'Nº de Coleções',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este grupo de coleções não pode ser excluído pois há coleções associadas.',
            ],
        ],
    ],
];
