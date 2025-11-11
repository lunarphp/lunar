<?php

return [

    'label' => 'Pedido',

    'plural_label' => 'Pedidos',

    'breadcrumb' => [
        'manage' => 'Gerenciar',
    ],

    'tabs' => [
        'all' => 'Todos',
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
            'label' => 'Referência do cliente',
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
            'label' => 'Tipo de cliente',
        ],
        'placed_after' => [
            'label' => 'Realizado após',
        ],
        'placed_before' => [
            'label' => 'Realizado antes',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Nome',
            ],
            'last_name' => [
                'label' => 'Sobrenome',
            ],
            'line_one' => [
                'label' => 'Endereço - linha 1',
            ],
            'line_two' => [
                'label' => 'Endereço - linha 2',
            ],
            'line_three' => [
                'label' => 'Endereço - linha 3',
            ],
            'company_name' => [
                'label' => 'Nome da empresa',
            ],
            'tax_identifier' => [
                'label' => 'Identificador fiscal',
            ],
            'contact_phone' => [
                'label' => 'Telefone',
            ],
            'contact_email' => [
                'label' => 'Endereço de e-mail',
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
            'label' => 'Valor',

            'hint' => [
                'less_than_total' => 'Você está prestes a capturar um valor menor que o total da transação',
            ],
        ],

        'notes' => [
            'label' => 'Notas',
        ],
        'confirm' => [
            'label' => 'Confirmar',

            'alert' => 'Confirmação necessária',

            'hint' => [
                'capture' => 'Confirme que deseja capturar este pagamento',
                'refund' => 'Confirme que deseja reembolsar este valor.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Notas',
            'placeholder' => 'Sem notas neste pedido',
        ],
        'delivery_instructions' => [
            'label' => 'Instruções de entrega',
        ],
        'shipping_total' => [
            'label' => 'Total de frete',
        ],
        'paid' => [
            'label' => 'Pago',
        ],
        'refund' => [
            'label' => 'Reembolso',
        ],
        'unit_price' => [
            'label' => 'Preço unitário',
        ],
        'quantity' => [
            'label' => 'Quantidade',
        ],
        'sub_total' => [
            'label' => 'Subtotal',
        ],
        'discount_total' => [
            'label' => 'Total de desconto',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Nível de estoque atual: :count',
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
            'label' => 'Referência do cliente',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Data de criação',
        ],
        'date_placed' => [
            'label' => 'Data do pedido',
        ],
        'new_returning' => [
            'label' => 'Novo / Recorrente',
        ],
        'new_customer' => [
            'label' => 'Cliente novo',
        ],
        'returning_customer' => [
            'label' => 'Cliente recorrente',
        ],
        'shipping_address' => [
            'label' => 'Endereço de entrega',
        ],
        'billing_address' => [
            'label' => 'Endereço de cobrança',
        ],
        'address_not_set' => [
            'label' => 'Nenhum endereço definido',
        ],
        'billing_matches_shipping' => [
            'label' => 'Igual ao endereço de entrega',
        ],
        'additional_info' => [
            'label' => 'Informações adicionais',
        ],
        'no_additional_info' => [
            'label' => 'Sem informações adicionais',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'timeline' => [
            'label' => 'Linha do tempo',
        ],
        'transactions' => [
            'label' => 'Transações',
            'placeholder' => 'Sem transações',
        ],
        'alert' => [
            'requires_capture' => 'Este pedido ainda requer captura do pagamento.',
            'partially_refunded' => 'Este pedido foi parcialmente reembolsado.',
            'refunded' => 'Este pedido foi reembolsado.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Atualizar status',
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
            'notification' => 'Baixando PDF do pedido',
        ],
        'edit_address' => [
            'label' => 'Editar',

            'notification' => [
                'error' => 'Erro',

                'billing_address' => [
                    'saved' => 'Endereço de cobrança salvo',
                ],

                'shipping_address' => [
                    'saved' => 'Endereço de entrega salvo',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Editar',
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separe tags pressionando Enter, Tab ou vírgula (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Capturar pagamento',

            'notification' => [
                'error' => 'Houve um problema na captura',
                'success' => 'Captura realizada com sucesso',
            ],
        ],
        'refund_payment' => [
            'label' => 'Reembolsar',

            'notification' => [
                'error' => 'Houve um problema no reembolso',
                'success' => 'Reembolso realizado com sucesso',
            ],
        ],
    ],

];
