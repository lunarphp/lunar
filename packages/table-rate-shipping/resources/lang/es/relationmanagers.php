<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Asocia grupos de clientes a este método de envío para determinar su disponibilidad.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Tarifas de Envío',
        'actions' => [
            'create' => [
                'label' => 'Crear Tarifa de Envío',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Todos los precios incluyen impuestos, que se tendrán en cuenta al calcular el gasto mínimo.',
            'prices_excl_tax' => 'Todos los precios excluyen impuestos, el gasto mínimo se basará en el subtotal del carrito.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Método de Envío',
            ],
            'price' => [
                'label' => 'Precio',
            ],
            'prices' => [
                'label' => 'Desglose de Precios',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Grupo de Clientes',
                        'placeholder' => 'Cualquiera',
                    ],
                    'currency_id' => [
                        'label' => 'Moneda',
                    ],
                    'min_quantity' => [
                        'label' => 'Gasto Mín.',
                    ],
                    'price' => [
                        'label' => 'Precio',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Método de Envío',
            ],
            'price' => [
                'label' => 'Precio',
            ],
            'price_breaks_count' => [
                'label' => 'Desglose de Precios',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Exclusiones de Envío',
        'form' => [
            'purchasable' => [
                'label' => 'Producto',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Agregar lista de exclusión de envío',
            ],
            'attach' => [
                'label' => 'Agregar lista de exclusión',
            ],
            'detach' => [
                'label' => 'Eliminar',
            ],
        ],
    ],
];
