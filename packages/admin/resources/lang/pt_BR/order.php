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
            'label' => 'Order',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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

    'fulfilments' => [
        'heading' => 'Fulfillments',
        'unreferenced' => 'Fulfillment #:id',
        'on_hold' => 'Em espera',
        'empty' => 'Nenhum fulfillment ainda.',
        'columns' => [
            'reference' => 'Referência',
            'state' => 'Estado',
            'items' => 'Itens',
            'tracking' => 'Rastreamento',
            'shipped_at' => 'Enviado em',
            'handed_over' => [
                'shipping' => 'Enviado em',
                'collection' => 'Retirado em',
                'digital' => 'Disponibilizado em',
            ],
            'handed_over_default' => 'Concluído em',
        ],
        'actions' => [
            'more' => 'Mais ações',
            'notify' => 'Notificar cliente',
            'add_tracking' => [
                'label' => 'Adicionar rastreamento',
                'modal_heading' => 'Adicionar rastreamento',
                'notification' => [
                    'success' => 'Rastreamento adicionado.',
                    'error' => 'Não foi possível adicionar o rastreamento.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Remover rastreamento',
                'notification' => [
                    'success' => 'Rastreamento removido.',
                    'error' => 'Não foi possível remover o rastreamento.',
                ],
            ],
            'create' => [
                'label' => 'Criar fulfillment',
                'modal_heading' => 'Criar fulfillment',
                'empty' => 'Todas as linhas já foram concluídas.',
                'notification' => [
                    'success' => 'Fulfillment criado.',
                    'error' => 'Não foi possível criar o fulfillment.',
                ],
            ],
            'ship' => [
                'label' => 'Marcar como enviado',
                'modal_heading' => 'Marcar fulfillment como enviado',
                'notification' => [
                    'success' => 'Fulfillment marcado como enviado.',
                    'error' => 'Não foi possível enviar o fulfillment.',
                ],
            ],
            'fulfil' => [
                'label' => 'Marcar como concluído',
                'modal_heading' => 'Marcar fulfillment como concluído',
                'labels' => [
                    'collection' => 'Marcar como retirado',
                ],
                'notification' => [
                    'success' => 'Fulfillment marcado como concluído.',
                    'error' => 'Não foi possível concluir o fulfillment.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancelar fulfillment',
                'modal_heading' => 'Cancelar fulfillment',
                'description' => 'Isto retorna o fulfillment para pendente, para que possa ser progredido novamente. Os detalhes de envio são removidos.',
                'notification' => [
                    'success' => 'Fulfillment cancelado.',
                    'error' => 'Não foi possível cancelar o fulfillment.',
                ],
            ],
            'change_location' => [
                'label' => 'Alterar local',
                'modal_heading' => 'Alterar local do fulfillment',
                'field' => 'Local',
                'notification' => [
                    'success' => 'Local do fulfillment atualizado.',
                    'error' => 'Não foi possível alterar o local do fulfillment.',
                ],
            ],
            'return' => [
                'label' => 'Devolver',
                'notification' => [
                    'success' => 'Fulfillment devolvido.',
                    'error' => 'Não foi possível devolver o fulfillment.',
                ],
            ],
            'update_status' => [
                'label' => 'Atualizar status',
            ],
            'transition' => [
                'modal_heading' => 'Marcar fulfillment como :status?',
                'notification' => [
                    'success' => 'Status do fulfillment atualizado.',
                    'error' => 'Não foi possível atualizar o status do fulfillment.',
                ],
            ],
            'undo_return' => [
                'label' => 'Desfazer devolução',
                'notification' => [
                    'success' => 'Devolução desfeita.',
                    'error' => 'Não foi possível desfazer a devolução.',
                ],
            ],
            'hold' => [
                'label' => 'Colocar fulfillment em espera',
                'modal_heading' => 'Colocar fulfillment em espera',
                'reason' => 'Motivo',
                'note' => 'Observação',
                'notification' => [
                    'success' => 'Fulfillment colocado em espera.',
                    'error' => 'Não foi possível colocar o fulfillment em espera.',
                ],
            ],
            'release' => [
                'label' => 'Liberar da espera',
                'notification' => [
                    'success' => 'Fulfillment liberado.',
                    'error' => 'Não foi possível liberar o fulfillment.',
                ],
            ],
            'split' => [
                'label' => 'Dividir',
                'confirm' => 'Dividir fulfillment',
                'cancel' => 'Cancelar',
                'empty' => 'Selecione uma quantidade para dividir.',
                'modal_heading' => 'Dividir fulfillment',
                'notification' => [
                    'success' => 'Fulfillment dividido.',
                    'error' => 'Não foi possível dividir o fulfillment.',
                ],
            ],
            'merge' => [
                'label' => 'Mesclar',
                'confirm' => 'Mesclar fulfillment',
                'cancel' => 'Cancelar',
                'modal_heading' => 'Mesclar fulfillment',
                'description' => 'Selecione os itens que deseja mesclar.',
                'target' => 'Mesclar com',
                'empty' => 'Selecione os itens e um destino para mesclar.',
                'notification' => [
                    'success' => 'Fulfillments mesclados.',
                    'error' => 'Não foi possível mesclar os fulfillments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantidade',
            'tracking' => 'Rastreamento',
            'tracking_item' => 'Rastreamento #:number',
            'unit_price' => 'Preço unitário',
            'sub_total' => 'Subtotal',
            'discount_total' => 'Total de desconto',
            'total' => 'Total',
            'stock_level' => 'Nível de estoque atual: :count',
            'of' => 'de :count',
            'outstanding' => 'Pendente: :count',
            'tracking_number' => 'Número de rastreamento',
            'tracking_url' => 'URL de rastreamento',
            'carrier' => 'Transportadora',
            'carrier_custom' => 'Personalizada / outra',
            'tracking_url_help' => 'Necessário apenas para transportadoras sem link de rastreamento automático.',
            'shipping_method' => 'Método de envio',
            'move_quantity' => 'Quantidade a mover',
        ],
    ],

    'other_items' => [
        'heading' => 'Outros itens',
    ],
];
