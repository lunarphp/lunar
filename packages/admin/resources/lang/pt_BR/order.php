<?php

return [

    'label' => 'Pedido',

    'plural_label' => 'Pedidos',

    'breadcrumb' => [
        'manage' => 'Gerenciar',
    ],

    'transactions' => [
        'capture' => 'Capturado',
        'intent' => 'Intenção',
        'refund' => 'Reembolsado',
        'failed' => 'Falhou',
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Referência',
        ],
        'customer_reference' => [
            'label' => 'Referência do Cliente',
        ],
        'customer' => [
            'label' => 'Cliente',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'postcode' => [
            'label' => 'CEP',
        ],
        'email' => [
            'label' => 'E-mail',
            'copy_message' => 'Endereço de e-mail copiado',
        ],
        'phone' => [
            'label' => 'Telefone',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Data',
        ],
        'new_customer' => [
            'label' => 'Tipo de Cliente',
        ],
        'placed_after' => [
            'label' => 'Colocado depois de',
        ],
        'placed_before' => [
            'label' => 'Colocado antes de',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Primeiro Nome',
            ],
            'last_name' => [
                'label' => 'Sobrenome',
            ],
            'line_one' => [
                'label' => 'Endereço Linha 1',
            ],
            'line_two' => [
                'label' => 'Endereço Linha 2',
            ],
            'line_three' => [
                'label' => 'Endereço Linha 3',
            ],
            'company_name' => [
                'label' => 'Nome da Empresa',
            ],
            'contact_phone' => [
                'label' => 'Telefone',
            ],
            'contact_email' => [
                'label' => 'Endereço de E-mail',
            ],
            'city' => [
                'label' => 'Cidade',
            ],
            'state' => [
                'label' => 'Estado / Província',
            ],
            'postcode' => [
                'label' => 'CEP',
            ],
            'country_id' => [
                'label' => 'País',
            ],
        ],

        'reference' => [
            'label' => 'Referência',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'transaction' => [
            'label' => 'Transação',
        ],
        'amount' => [
            'label' => 'Quantia',

            'hint' => [
                'less_than_total' => "Você está prestes a capturar um valor menor que o valor total da transação",
            ],
        ],

        'notes' => [
            'label' => 'Notas',
        ],
        'confirm' => [
            'label' => 'Confirmar',

            'alert' => 'Confirmação necessária',

            'hint' => [
                'capture' => 'Por favor, confirme que deseja capturar este pagamento',
                'refund' => 'Por favor, confirme que deseja reembolsar este valor.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Notas',
            'placeholder' => 'Sem notas sobre este pedido',
        ],
        'delivery_instructions' => [
            'label' => 'Instruções de Entrega',
        ],
        'shipping_total' => [
            'label' => 'Total de Envio',
        ],
        'paid' => [
            'label' => 'Pago',
        ],
        'refund' => [
            'label' => 'Reembolso',
        ],
        'unit_price' => [
            'label' => 'Preço Unitário',
        ],
        'quantity' => [
            'label' => 'Quantidade',
        ],
        'sub_total' => [
            'label' => 'Sub Total',
        ],
        'discount_total' => [
            'label' => 'Desconto Total',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Nível de Estoque Atual: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'no momento do pedido: :count',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Referência',
        ],
        'customer_reference' => [
            'label' => 'Referência do Cliente',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Data de Criação',
        ],
        'date_placed' => [
            'label' => 'Data de Colocação',
        ],
        'new_returning' =>

 [
            'label' => 'Novo / Retornando',
        ],
        'new_customer' => [
            'label' => 'Novo Cliente',
        ],
        'returning_customer' => [
            'label' => 'Cliente Retornando',
        ],
        'shipping_address' => [
            'label' => 'Endereço de Envio',
        ],
        'billing_address' => [
            'label' => 'Endereço de Cobrança',
        ],
        'address_not_set' => [
            'label' => 'Nenhum endereço definido',
        ],
        'billing_matches_shipping' => [
            'label' => 'Mesmo que o endereço de envio',
        ],
        'additional_info' => [
            'label' => 'Informação Adicional',
        ],
        'no_additional_info' => [
            'label' => 'Nenhuma Informação Adicional',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'timeline' => [
            'label' => 'Cronograma',
        ],
        'transactions' => [
            'label' => 'Transações',
            'placeholder' => 'Nenhuma transação',
        ],
        'alert' => [
            'requires_capture' => 'Este pedido ainda requer que o pagamento seja capturado.',
            'partially_refunded' => 'Este pedido foi parcialmente reembolsado.',
            'refunded' => 'Este pedido foi reembolsado.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Atualizar Status',
            'notification' => 'Status dos pedidos atualizado',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Novo status',
            ],
            'additional_content' => [
                'label' => 'Conteúdo adicional',
            ],
            'additional_email_recipient' => [
                'label' => 'Destinatário de e-mail adicional',
                'placeholder' => 'opcional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Baixar PDF',
            'notification' => 'Download do PDF do pedido',
        ],
        'edit_address' => [
            'label' => 'Editar',

            'notification' => [
                'error' => 'Erro',

                'billing_address' => [
                    'saved' => 'Endereço de cobrança salvo',
                ],

                'shipping_address' => [
                    'saved' => 'Endereço de envio salvo',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Editar',
        ],
        'capture_payment' => [
            'label' => 'Capturar Pagamento',

            'notification' => [
                'error' => 'Houve um problema com a captura',
                'success' => 'Captura bem-sucedida',
            ],
        ],
        'refund_payment' => [
            'label' => 'Reembolsar',

            'notification' => [
                'error' => 'Houve um problema com o reembolso',
                'success' => 'Reembolso bem-sucedido',
            ],
        ],
    ],

];
