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
        'heading' => 'Fulfilments',
        'unreferenced' => 'Fulfilment #:id',
        'on_hold' => 'On hold',
        'empty' => 'No fulfilments yet.',
        'columns' => [
            'reference' => 'Reference',
            'state' => 'State',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Shipped at',
            'handed_over' => [
                'shipping' => 'Shipped at',
                'collection' => 'Collected at',
                'digital' => 'Provisioned at',
            ],
            'handed_over_default' => 'Fulfilled at',
        ],
        'actions' => [
            'more' => 'More actions',
            'add_tracking' => [
                'label' => 'Add tracking',
                'modal_heading' => 'Add tracking',
                'notification' => [
                    'success' => 'Tracking added.',
                    'error' => 'Could not add tracking.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Remove tracking',
                'notification' => [
                    'success' => 'Tracking removed.',
                    'error' => 'Could not remove tracking.',
                ],
            ],
            'create' => [
                'label' => 'Create fulfilment',
                'modal_heading' => 'Create fulfilment',
                'empty' => 'Every line is already fulfilled.',
                'notification' => [
                    'success' => 'Fulfilment created.',
                    'error' => 'Could not create fulfilment.',
                ],
            ],
            'ship' => [
                'label' => 'Mark shipped',
                'modal_heading' => 'Mark fulfilment as shipped',
                'notification' => [
                    'success' => 'Fulfilment marked as shipped.',
                    'error' => 'Could not ship fulfilment.',
                ],
            ],
            'fulfil' => [
                'label' => 'Mark fulfilled',
                'modal_heading' => 'Mark fulfilment as fulfilled',
                'labels' => [
                    'collection' => 'Mark collected',
                ],
                'notification' => [
                    'success' => 'Fulfilment marked as fulfilled.',
                    'error' => 'Could not fulfil fulfilment.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancel fulfilment',
                'modal_heading' => 'Cancel fulfilment',
                'description' => 'This returns the fulfilment to pending so it can be progressed again. Any shipment details are cleared.',
                'notification' => [
                    'success' => 'Fulfilment cancelled.',
                    'error' => 'Could not cancel fulfilment.',
                ],
            ],
            'change_location' => [
                'label' => 'Change location',
                'modal_heading' => 'Change fulfilment location',
                'field' => 'Location',
                'notification' => [
                    'success' => 'Fulfilment location updated.',
                    'error' => 'Could not change the fulfilment location.',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'notification' => [
                    'success' => 'Fulfilment returned.',
                    'error' => 'Could not return fulfilment.',
                ],
            ],
            'update_status' => [
                'label' => 'Update status',
            ],
            'transition' => [
                'modal_heading' => 'Mark fulfilment as :status?',
                'notification' => [
                    'success' => 'Fulfilment status updated.',
                    'error' => 'Could not update the fulfilment status.',
                ],
            ],
            'undo_return' => [
                'label' => 'Undo return',
                'notification' => [
                    'success' => 'Return undone.',
                    'error' => 'Could not undo the return.',
                ],
            ],
            'hold' => [
                'label' => 'Hold fulfilment',
                'modal_heading' => 'Hold fulfilment',
                'reason' => 'Reason',
                'note' => 'Note',
                'notification' => [
                    'success' => 'Fulfilment placed on hold.',
                    'error' => 'Could not hold the fulfilment.',
                ],
            ],
            'release' => [
                'label' => 'Release hold',
                'notification' => [
                    'success' => 'Fulfilment released.',
                    'error' => 'Could not release the fulfilment.',
                ],
            ],
            'split' => [
                'label' => 'Split',
                'confirm' => 'Split fulfilment',
                'cancel' => 'Cancel',
                'empty' => 'Select a quantity to split out.',
                'modal_heading' => 'Split fulfilment',
                'notification' => [
                    'success' => 'Fulfilment split.',
                    'error' => 'Could not split fulfilment.',
                ],
            ],
            'merge' => [
                'label' => 'Merge',
                'confirm' => 'Merge fulfilment',
                'cancel' => 'Cancel',
                'modal_heading' => 'Merge fulfilment',
                'description' => 'Select the items you would like to merge.',
                'target' => 'Merge with',
                'empty' => 'Select items and a destination to merge.',
                'notification' => [
                    'success' => 'Fulfilments merged.',
                    'error' => 'Could not merge fulfilments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantity',
            'tracking' => 'Tracking',
            'tracking_item' => 'Tracking #:number',
            'unit_price' => 'Unit Price',
            'sub_total' => 'Sub Total',
            'discount_total' => 'Discount Total',
            'total' => 'Total',
            'stock_level' => 'Current Stock Level: :count',
            'of' => 'of :count',
            'outstanding' => 'Outstanding: :count',
            'tracking_number' => 'Tracking number',
            'tracking_url' => 'Tracking URL',
            'carrier' => 'Carrier',
            'carrier_custom' => 'Custom / other',
            'tracking_url_help' => 'Only needed for carriers without an automatic tracking link.',
            'shipping_method' => 'Shipping method',
            'move_quantity' => 'Quantity to move out',
        ],
    ],

    'other_items' => [
        'heading' => 'Other items',
    ],
];
