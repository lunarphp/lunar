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
        'heading' => 'Cumplimientos',
        'unreferenced' => 'Cumplimiento n.º :id',
        'on_hold' => 'En espera',
        'empty' => 'Aún no hay cumplimientos.',
        'columns' => [
            'reference' => 'Referencia',
            'state' => 'Estado',
            'items' => 'Artículos',
            'tracking' => 'Seguimiento',
            'shipped_at' => 'Enviado el',
            'handed_over' => [
                'shipping' => 'Enviado el',
                'collection' => 'Recogido el',
                'digital' => 'Aprovisionado el',
            ],
            'handed_over_default' => 'Cumplido el',
        ],
        'actions' => [
            'more' => 'Más acciones',
            'notify' => 'Notificar al cliente',
            'add_tracking' => [
                'label' => 'Añadir seguimiento',
                'modal_heading' => 'Añadir seguimiento',
                'notification' => [
                    'success' => 'Seguimiento añadido.',
                    'error' => 'No se pudo añadir el seguimiento.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Eliminar seguimiento',
                'notification' => [
                    'success' => 'Seguimiento eliminado.',
                    'error' => 'No se pudo eliminar el seguimiento.',
                ],
            ],
            'create' => [
                'label' => 'Crear cumplimiento',
                'modal_heading' => 'Crear cumplimiento',
                'empty' => 'Todas las líneas ya están cumplidas.',
                'notification' => [
                    'success' => 'Cumplimiento creado.',
                    'error' => 'No se pudo crear el cumplimiento.',
                ],
            ],
            'ship' => [
                'label' => 'Marcar como enviado',
                'modal_heading' => 'Marcar el cumplimiento como enviado',
                'notification' => [
                    'success' => 'Cumplimiento marcado como enviado.',
                    'error' => 'No se pudo enviar el cumplimiento.',
                ],
            ],
            'fulfil' => [
                'label' => 'Marcar como cumplido',
                'modal_heading' => 'Marcar el cumplimiento como cumplido',
                'labels' => [
                    'collection' => 'Marcar como recogido',
                ],
                'notification' => [
                    'success' => 'Cumplimiento marcado como cumplido.',
                    'error' => 'No se pudo cumplir el cumplimiento.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancelar cumplimiento',
                'modal_heading' => 'Cancelar cumplimiento',
                'description' => 'Esto devuelve el cumplimiento a pendiente para que se pueda volver a tramitar. Se eliminan todos los detalles del envío.',
                'notification' => [
                    'success' => 'Cumplimiento cancelado.',
                    'error' => 'No se pudo cancelar el cumplimiento.',
                ],
            ],
            'change_location' => [
                'label' => 'Cambiar ubicación',
                'modal_heading' => 'Cambiar la ubicación del cumplimiento',
                'field' => 'Ubicación',
                'notification' => [
                    'success' => 'Ubicación del cumplimiento actualizada.',
                    'error' => 'No se pudo cambiar la ubicación del cumplimiento.',
                ],
            ],
            'return' => [
                'label' => 'Devolver',
                'notification' => [
                    'success' => 'Cumplimiento devuelto.',
                    'error' => 'No se pudo devolver el cumplimiento.',
                ],
            ],
            'update_status' => [
                'label' => 'Actualizar estado',
            ],
            'transition' => [
                'modal_heading' => '¿Marcar el cumplimiento como :status?',
                'notification' => [
                    'success' => 'Estado del cumplimiento actualizado.',
                    'error' => 'No se pudo actualizar el estado del cumplimiento.',
                ],
            ],
            'undo_return' => [
                'label' => 'Deshacer devolución',
                'notification' => [
                    'success' => 'Devolución deshecha.',
                    'error' => 'No se pudo deshacer la devolución.',
                ],
            ],
            'hold' => [
                'label' => 'Poner en espera',
                'modal_heading' => 'Poner el cumplimiento en espera',
                'reason' => 'Motivo',
                'note' => 'Nota',
                'notification' => [
                    'success' => 'Cumplimiento puesto en espera.',
                    'error' => 'No se pudo poner el cumplimiento en espera.',
                ],
            ],
            'release' => [
                'label' => 'Liberar de la espera',
                'notification' => [
                    'success' => 'Cumplimiento liberado.',
                    'error' => 'No se pudo liberar el cumplimiento.',
                ],
            ],
            'split' => [
                'label' => 'Dividir',
                'confirm' => 'Dividir cumplimiento',
                'cancel' => 'Cancelar',
                'empty' => 'Selecciona una cantidad para separar.',
                'modal_heading' => 'Dividir cumplimiento',
                'notification' => [
                    'success' => 'Cumplimiento dividido.',
                    'error' => 'No se pudo dividir el cumplimiento.',
                ],
            ],
            'merge' => [
                'label' => 'Combinar',
                'confirm' => 'Combinar cumplimiento',
                'cancel' => 'Cancelar',
                'modal_heading' => 'Combinar cumplimiento',
                'description' => 'Selecciona los artículos que deseas combinar.',
                'target' => 'Combinar con',
                'empty' => 'Selecciona artículos y un destino para combinar.',
                'notification' => [
                    'success' => 'Cumplimientos combinados.',
                    'error' => 'No se pudieron combinar los cumplimientos.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Cantidad',
            'tracking' => 'Seguimiento',
            'tracking_item' => 'Seguimiento n.º :number',
            'unit_price' => 'Precio unitario',
            'sub_total' => 'Subtotal',
            'discount_total' => 'Total de descuento',
            'total' => 'Total',
            'stock_level' => 'Nivel de existencias actual: :count',
            'of' => 'de :count',
            'outstanding' => 'Pendiente: :count',
            'tracking_number' => 'Número de seguimiento',
            'tracking_url' => 'URL de seguimiento',
            'carrier' => 'Transportista',
            'carrier_custom' => 'Personalizado / otro',
            'tracking_url_help' => 'Solo es necesario para transportistas sin un enlace de seguimiento automático.',
            'shipping_method' => 'Método de envío',
            'move_quantity' => 'Cantidad a separar',
        ],
    ],

    'other_items' => [
        'heading' => 'Otros artículos',
    ],
];
