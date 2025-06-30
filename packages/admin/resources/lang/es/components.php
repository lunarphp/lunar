<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Etiquetas actualizadas',
        ],
    ],

    'activity-log' => [
        'input' => [
            'placeholder' => 'Agregar un comentario',
        ],

        'action' => [
            'add-comment' => 'Agregar Comentario',
        ],

        'system' => 'Sistema',

        'partials' => [
            'orders' => [
                'order_created' => 'Pedido Creado',

                'status_change' => 'Estado actualizado',

                'capture' => 'Pago de :amount con tarjeta terminando en :last_four',

                'authorized' => 'Autorizado de :amount con tarjeta terminando en :last_four',

                'refund' => 'Reembolso de :amount con tarjeta terminando en :last_four',

                'address' => ':type actualizado',

                'billingAddress' => 'Dirección de facturación',

                'shippingAddress' => 'Dirección de envío',
            ],

            'update' => [
                'updated' => ':model actualizado',
            ],

            'create' => [
                'created' => ':model creado',
            ],

            'tags' => [
                'updated' => 'Etiquetas actualizadas',
                'added' => 'Agregado',
                'removed' => 'Eliminado',
            ],
        ],

        'notification' => [
            'comment_added' => 'Comentario agregado',
        ],
    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Ingresa el ID del video de YouTube. ej. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Colección Padre',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Colecciones Reordenadas',
            ],
            'node-expanded' => [
                'danger' => 'No se pueden cargar las colecciones',
            ],
            'delete' => [
                'danger' => 'No se puede eliminar la colección',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Agregar Opción',
        ],
        'delete-option' => [
            'label' => 'Eliminar Opción',
        ],
        'remove-shared-option' => [
            'label' => 'Eliminar Opción Compartida',
        ],
        'add-value' => [
            'label' => 'Agregar Otro Valor',
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'values' => [
            'label' => 'Valores',
        ],
    ],
];
