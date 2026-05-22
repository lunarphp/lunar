<?php

return [
    'label_plural' => 'Métodos de envio',
    'label' => 'Método de envio',
    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'description' => [
            'label' => 'Descrição',
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
            'label' => 'Cobrar por',
            'options' => [
                'cart_total' => 'Total do carrinho',
                'weight' => 'Peso',
            ],
        ],
        'driver' => [
            'label' => 'Tipo',
            'options' => [
                'ship-by' => 'Padrão',
                'collection' => 'Coleta',
            ],
        ],
        'stock_available' => [
            'label' => 'Estoque de todos os itens do carrinho deve estar disponível',
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
            'label' => 'Nome',
        ],
        'code' => [
            'label' => 'Código',
        ],
        'driver' => [
            'label' => 'Tipo',
            'options' => [
                'ship-by' => 'Padrão',
                'collection' => 'Coleta',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilidade',
            'customer_groups' => 'Este método de envio está indisponível para todos os grupos de clientes.',
        ],
    ],
];
