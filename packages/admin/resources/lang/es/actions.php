<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Crear colección raíz',
        ],
        'create_child' => [
            'label' => 'Crear colección secundaria',
        ],
        'move' => [
            'label' => 'Mover colección',
        ],
        'delete' => [
            'label' => 'Eliminar',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'No se puede eliminar',
                    'body' => 'Esta colección tiene colecciones secundarias y no se puede eliminar.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Actualizar estado',
            'wizard' => [
                'step_one' => [
                    'label' => 'Estado',
                ],
                'step_two' => [
                    'label' => 'Correos y notificaciones',
                    'no_mailers' => 'No hay correos disponibles para este estado.',
                ],
                'step_three' => [
                    'label' => 'Previsualizar y guardar',
                    'no_mailers' => 'No se han seleccionado correos para la previsualización.',
                ],
            ],
            'notification' => [
                'label' => 'Estado del pedido actualizado',
            ],
            'billing_email' => [
                'label' => 'Correo de facturación',
            ],
            'shipping_email' => [
                'label' => 'Correo de envío',
            ],
        ],

    ],
];
