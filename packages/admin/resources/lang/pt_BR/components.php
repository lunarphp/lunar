<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Tags atualizadas',
        ],
    ],

    'activity-log' => [
        'input' => [
            'placeholder' => 'Adicionar um comentário',
        ],

        'action' => [
            'add-comment' => 'Adicionar Comentário',
        ],

        'system' => 'Sistema',

        'partials' => [
            'orders' => [
                'order_created' => 'Pedido Criado',

                'status_change' => 'Status atualizado',

                'capture' => 'Pagamento de :amount no cartão terminando em :last_four',

                'authorized' => 'Autorizado de :amount no cartão terminando em :last_four',

                'refund' => 'Reembolso de :amount no cartão terminando em :last_four',

                'address' => ':type atualizado',

                'billingAddress' => 'Endereço de Cobrança',

                'shippingAddress' => 'Endereço de Entrega',
            ],

            'update' => [
                'updated' => ':model atualizado',
            ],

            'create' => [
                'created' => ':model criado',
            ],

            'tags' => [
                'updated' => 'Tags atualizadas',
                'added' => 'Adicionado',
                'removed' => 'Removido',
            ],
        ],

        'notification' => [
            'comment_added' => 'Comentário adicionado',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Digite o ID do vídeo do YouTube. Ex: dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Coleção Pai',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Coleções Reordenadas',
            ],
            'node-expanded' => [
                'danger' => 'Não foi possível carregar as coleções',
            ],
            'delete' => [
                'danger' => 'Não foi possível excluir a coleção',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Adicionar Opção',
        ],
        'delete-option' => [
            'label' => 'Excluir Opção',
        ],
        'remove-shared-option' => [
            'label' => 'Remover Opção Compartilhada',
        ],
        'add-value' => [
            'label' => 'Adicionar Outro Valor',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'values' => [
            'label' => 'Valores',
        ],
    ],
];
