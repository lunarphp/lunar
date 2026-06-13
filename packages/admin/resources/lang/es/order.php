<?php

return [
    'label' => 'Pedido',
    'plural_label' => 'Pedidos',
    'breadcrumb' => [
        'manage' => 'Gestionar',
    ],
    'transactions' => [
        'capture' => 'Capturado',
        'intent' => 'Intención',
        'refund' => 'Reembolsado',
        'failed' => 'Fallido',
    ],
    'table' => [
        'status' => [
            'label' => 'Estado',
        ],
        'reference' => [
            'label' => 'Referencia',
        ],
        'customer_reference' => [
            'label' => 'Referencia del Cliente',
        ],
        'customer' => [
            'label' => 'Cliente',
        ],
        'tags' => [
            'label' => 'Etiquetas',
        ],
        'postcode' => [
            'label' => 'Código Postal',
        ],
        'email' => [
            'label' => 'Correo Electrónico',
            'copy_message' => 'Dirección de correo electrónico copiada',
        ],
        'phone' => [
            'label' => 'Teléfono',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Fecha',
        ],
        'new_customer' => [
            'label' => 'Tipo de Cliente',
        ],
        'placed_after' => [
            'label' => 'Realizado después de',
        ],
        'placed_before' => [
            'label' => 'Realizado antes de',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Nombre',
            ],
            'last_name' => [
                'label' => 'Apellido',
            ],
            'line_one' => [
                'label' => 'Dirección Línea 1',
            ],
            'line_two' => [
                'label' => 'Dirección Línea 2',
            ],
            'line_three' => [
                'label' => 'Dirección Línea 3',
            ],
            'company_name' => [
                'label' => 'Nombre de la Empresa',
            ],
            'tax_identifier' => [
                'label' => 'Identificador Fiscal',
            ],
            'contact_phone' => [
                'label' => 'Teléfono',
            ],
            'contact_email' => [
                'label' => 'Correo Electrónico',
            ],
            'city' => [
                'label' => 'Ciudad',
            ],
            'state' => [
                'label' => 'Estado / Provincia',
            ],
            'postcode' => [
                'label' => 'Código Postal',
            ],
            'country_id' => [
                'label' => 'País',
            ],
        ],
        'reference' => [
            'label' => 'Referencia',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Transacción',
        ],
        'amount' => [
            'label' => 'Cantidad',
            'hint' => [
                'less_than_total' => 'Está a punto de capturar un monto menor al valor total de la transacción',
            ],
        ],
        'notes' => [
            'label' => 'Notas',
        ],
        'confirm' => [
            'label' => 'Confirmar',
            'alert' => 'Se requiere confirmación',
            'hint' => [
                'capture' => 'Por favor confirme que desea capturar este pago',
                'refund' => 'Por favor confirme que desea reembolsar esta cantidad.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Notas',
            'placeholder' => 'Sin notas en este pedido',
        ],
        'delivery_instructions' => [
            'label' => 'Instrucciones de Entrega',
        ],
        'shipping_total' => [
            'label' => 'Total de Envío',
        ],
        'paid' => [
            'label' => 'Pagado',
        ],
        'refund' => [
            'label' => 'Reembolso',
        ],
        'unit_price' => [
            'label' => 'Precio Unitario',
        ],
        'quantity' => [
            'label' => 'Cantidad',
        ],
        'sub_total' => [
            'label' => 'Subtotal',
        ],
        'discount_total' => [
            'label' => 'Total de Descuentos',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Nivel de Stock Actual: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'al momento de hacer el pedido: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Referencia',
        ],
        'customer_reference' => [
            'label' => 'Referencia del Cliente',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Fecha de Creación',
        ],
        'date_placed' => [
            'label' => 'Fecha de Pedido',
        ],
        'new_returning' => [
            'label' => 'Nuevo / Recurrente',
        ],
        'new_customer' => [
            'label' => 'Nuevo Cliente',
        ],
        'returning_customer' => [
            'label' => 'Cliente Recurrente',
        ],
        'shipping_address' => [
            'label' => 'Dirección de Envío',
        ],
        'billing_address' => [
            'label' => 'Dirección de Facturación',
        ],
        'address_not_set' => [
            'label' => 'No se ha establecido dirección',
        ],
        'billing_matches_shipping' => [
            'label' => 'Igual que la dirección de envío',
        ],
        'additional_info' => [
            'label' => 'Información Adicional',
        ],
        'no_additional_info' => [
            'label' => 'Sin Información Adicional',
        ],
        'tags' => [
            'label' => 'Etiquetas',
        ],
        'timeline' => [
            'label' => 'Cronología',
        ],
        'transactions' => [
            'label' => 'Transacciones',
            'placeholder' => 'Sin transacciones',
        ],
        'alert' => [
            'requires_capture' => 'Este pedido aún requiere que se capture el pago.',
            'partially_refunded' => 'Este pedido ha sido parcialmente reembolsado.',
            'refunded' => 'Este pedido ha sido reembolsado.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Actualizar Estado',
            'notification' => 'Estado de pedidos actualizado',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'Nuevo estado',
            ],
            'additional_content' => [
                'label' => 'Contenido adicional',
            ],
            'additional_email_recipient' => [
                'label' => 'Destinatario adicional de correo electrónico',
                'placeholder' => 'opcional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Descargar PDF',
            'notification' => 'Descargando PDF del pedido',
        ],
        'edit_address' => [
            'label' => 'Editar',
            'notification' => [
                'error' => 'Error',
                'billing_address' => [
                    'saved' => 'Dirección de facturación guardada',
                ],
                'shipping_address' => [
                    'saved' => 'Dirección de envío guardada',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Editar',
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Capturar Pago',
            'notification' => [
                'error' => 'Hubo un problema con la captura',
                'success' => 'Captura exitosa',
            ],
        ],
        'refund_payment' => [
            'label' => 'Reembolsar',
            'notification' => [
                'error' => 'Hubo un problema con el reembolso',
                'success' => 'Reembolso exitoso',
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
