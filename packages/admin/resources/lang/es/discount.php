<?php

return [
    'plural_label' => 'Descuentos',
    'label' => 'Descuento',
    'form' => [
        'conditions' => [
            'heading' => 'Condiciones',
        ],
        'buy_x_get_y' => [
            'heading' => 'Compra X Obtén Y',
        ],
        'amount_off' => [
            'heading' => 'Cantidad Descontada',
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'starts_at' => [
            'label' => 'Fecha de Inicio',
        ],
        'ends_at' => [
            'label' => 'Fecha de Fin',
        ],
        'priority' => [
            'label' => 'Prioridad',
            'helper_text' => 'Los descuentos con mayor prioridad se aplicarán primero.',
            'options' => [
                'low' => [
                    'label' => 'Baja',
                ],
                'medium' => [
                    'label' => 'Media',
                ],
                'high' => [
                    'label' => 'Alta',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Detener otros descuentos después de este',
        ],
        'coupon' => [
            'label' => 'Cupón',
            'helper_text' => 'Introduce el cupón necesario para que se aplique el descuento, si se deja en blanco se aplicará automáticamente.',
        ],
        'max_uses' => [
            'label' => 'Máximo de usos',
            'helper_text' => 'Deja en blanco para usos ilimitados.',
        ],
        'max_uses_per_user' => [
            'label' => 'Máximo de usos por usuario',
            'helper_text' => 'Deja en blanco para usos ilimitados.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Monto Mínimo del Carrito',
        ],
        'min_qty' => [
            'label' => 'Cantidad de Producto',
            'helper_text' => 'Establece cuántos productos calificativos son necesarios para que se aplique el descuento.',
        ],
        'reward_qty' => [
            'label' => 'No. de artículos gratuitos',
            'helper_text' => 'Cuántos de cada artículo tienen descuento.',
        ],
        'max_reward_qty' => [
            'label' => 'Cantidad máxima de recompensa',
            'helper_text' => 'La cantidad máxima de productos que se pueden descontar, independientemente de los criterios.',
        ],
        'automatic_rewards' => [
            'label' => 'Agregar recompensas automáticamente',
            'helper_text' => 'Activa para agregar productos de recompensa cuando no estén presentes en el carrito.',
        ],
        'fixed_value' => [
            'label' => 'Valor fijo',
        ],
        'percentage' => [
            'label' => 'Porcentaje',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'status' => [
            'label' => 'Estado',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Activo',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'Pendiente',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Expirado',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Programado',
            ],
        ],
        'type' => [
            'label' => 'Tipo',
        ],
        'starts_at' => [
            'label' => 'Fecha de Inicio',
        ],
        'ends_at' => [
            'label' => 'Fecha de Fin',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilidad',
        ],
        'edit' => [
            'title' => 'Información básica',
        ],
        'limitations' => [
            'label' => 'Limitaciones',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Colecciones',
            'description' => 'Selecciona a qué colecciones se debe limitar este descuento.',
            'actions' => [
                'attach' => [
                    'label' => 'Adjuntar Colección',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitación',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusión',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitación',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusión',
                        ],
                    ],
                ],
            ],
        ],
        'brands' => [
            'title' => 'Marcas',
            'description' => 'Selecciona a qué marcas se debe limitar este descuento.',
            'actions' => [
                'attach' => [
                    'label' => 'Adjuntar Marca',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitación',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusión',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitación',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusión',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Productos',
            'description' => 'Selecciona a qué productos se debe limitar este descuento.',
            'actions' => [
                'attach' => [
                    'label' => 'Agregar Producto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitación',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusión',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitación',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusión',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Recompensas de Productos',
            'description' => 'Selecciona qué productos serán descontados si existen en el carrito y se cumplen las condiciones anteriores.',
            'actions' => [
                'attach' => [
                    'label' => 'Agregar Producto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitación',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusión',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitación',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusión',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Condiciones de Producto',
            'description' => 'Selecciona los productos necesarios para que se aplique el descuento.',
            'actions' => [
                'attach' => [
                    'label' => 'Agregar Producto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitación',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusión',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitación',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusión',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Variantes de Productos',
            'description' => 'Selecciona qué variantes de productos se debe limitar a este descuento.',
            'actions' => [
                'attach' => [
                    'label' => 'Agregar Variante de Producto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nombre',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Opción(es)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitación',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusión',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
