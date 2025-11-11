<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Criar coleção raiz',
        ],
        'create_child' => [
            'label' => 'Criar coleção filha',
        ],
        'move' => [
            'label' => 'Mover coleção',
        ],
        'delete' => [
            'label' => 'Excluir',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Não é possível excluir',
                    'body' => 'Esta coleção possui coleções filhas e não pode ser excluída.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Atualizar status',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'E-mails e notificações',
                    'no_mailers' => 'Não há e-mails disponíveis para este status.',
                ],
                'step_three' => [
                    'label' => 'Pré-visualizar e salvar',
                    'no_mailers' => 'Nenhum e-mail foi escolhido para pré-visualização.',
                ],
            ],
            'notification' => [
                'label' => 'Status do pedido atualizado',
            ],
            'billing_email' => [
                'label' => 'E-mail de cobrança',
            ],
            'shipping_email' => [
                'label' => 'E-mail de entrega',
            ],
        ],

    ],
];
