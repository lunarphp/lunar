<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Criar Coleção Raiz',
        ],
        'create_child' => [
            'label' => 'Criar Coleção Filha',
        ],
        'move' => [
            'label' => 'Mover Coleção',
        ],
        'delete' => [
            'label' => 'Excluir',
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Atualizar Status',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'E-mails e Notificações',
                    'no_mailers' => 'Não há sistema de envio de e-mails disponíveis para este status.',
                ],
                'step_three' => [
                    'label' => 'Visualizar e Salvar',
                    'no_mailers' => 'Nenhum sistema de envio de e-mails foi escolhido para visualização.',
                ],
            ],
            'notification' => [
                'label' => 'Status do pedido atualizado',
            ],
            'billing_email' => [
                'label' => 'E-mail de Cobrança',
            ],
            'shipping_email' => [
                'label' => 'E-mail de Entrega',
            ],
        ],

    ],
];
