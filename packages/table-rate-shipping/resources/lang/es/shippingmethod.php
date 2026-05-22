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
        'schedule' => [
            'label' => 'Availability Schedule',
            'days' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'from' => [
                'label' => 'From',
            ],
            'to' => [
                'label' => 'Until',
                'validation' => [
                    'after' => 'The until time must be after the from time.',
                ],
            ],
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
            ],
        ],
        'stock_available' => [
            'label' => 'El stock de todos los artículos del carrito debe estar disponible',
        ],
        'weight_unit' => [
            'label' => 'Weight Unit',
            'placeholder' => 'No weight restriction',
        ],
        'min_weight' => [
            'label' => 'Minimum Weight',
        ],
        'max_weight' => [
            'label' => 'Maximum Weight',
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
