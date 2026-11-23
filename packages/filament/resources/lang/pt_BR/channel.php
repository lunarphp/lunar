<?php

return [
    'label' => 'Canal',
    'plural_label' => 'Canais',
    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Padrão',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Padrão',
        ],
    ],

    'actions' => [
        'delete' => [
            'blocked' => 'O canal padrão, os canais com histórico de pedidos e os canais que ainda são usados por uma região ou um carrinho não podem ser excluídos. Marque o canal como inativo em vez disso.',
        ],
    ],

];
