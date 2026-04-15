<?php

return [

    'label' => 'Classe de imposto',

    'plural_label' => 'Classes de imposto',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'default' => [
            'label' => 'Padrão',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'default' => [
            'label' => 'Padrão',
        ],
    ],
    'delete' => [
        'error' => [
            'title' => 'Não é possível excluir a classe de imposto',
            'body' => 'Esta classe de imposto possui variações de produto associadas e não pode ser excluída.',
        ],
    ],
];
