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

            'add-comment' => 'Adicionar comentário',

        ],

        'system' => 'Sistema',

        'partials' => [
            'orders' => [
                'order_created' => 'Pedido criado',

                'status_change' => 'Status atualizado',

                'capture' => 'Pagamento de :amount no cartão com final :last_four',

                'authorized' => 'Autorizado o valor de :amount no cartão com final :last_four',

                'refund' => 'Reembolso de :amount no cartão com final :last_four',

                'address' => ':type atualizado',

                'billingAddress' => 'Endereço de cobrança',

                'shippingAddress' => 'Endereço de entrega',
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
            'helperText' => 'Informe o ID do vídeo do YouTube. Ex.: dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Coleção pai',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Coleções reordenadas',
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
            'label' => 'Adicionar opção',
        ],
        'delete-option' => [
            'label' => 'Excluir opção',
        ],
        'remove-shared-option' => [
            'label' => 'Remover opção compartilhada',
        ],
        'add-value' => [
            'label' => 'Adicionar outro valor',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'values' => [
            'label' => 'Valores',
        ],
    ],
];
