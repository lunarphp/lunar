<?php

return [

    'label' => 'Grupo de coleções',

    'plural_label' => 'Grupos de coleções',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'collections_count' => [
            'label' => 'Qtd. de coleções',
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
