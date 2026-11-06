<?php

return [
    'label_plural' => 'Métodos de Envío',
    'label' => 'Método de Envío',
    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'description' => [
            'label' => 'Descripción',
        ],
        'code' => [
            'label' => 'Código',
        ],
        'cutoff' => [
            'label' => 'Corte',
        ],
        'charge_by' => [
            'label' => 'Cargar Por',
            'options' => [
                'cart_total' => 'Total del Carrito',
                'weight' => 'Peso',
            ],
        ],
        'driver' => [
            'label' => 'Tipo',
            'options' => [
                'ship-by' => 'Estándar',
                'collection' => 'Recogida',
                'flat-rate' => 'Tarifa plana',
                'free-shipping' => 'Envío gratuito',
            ],
        ],
        'stock_available' => [
            'label' => 'El stock de todos los artículos del carrito debe estar disponible',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'code' => [
            'label' => 'Código',
        ],
        'driver' => [
            'label' => 'Tipo',
            'options' => [
                'ship-by' => 'Estándar',
                'collection' => 'Recogida',
                'flat-rate' => 'Tarifa plana',
                'free-shipping' => 'Envío gratuito',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilidad',
            'customer_groups' => 'Este método de envío no está disponible actualmente para todos los grupos de clientes.',
        ],
    ],
];
