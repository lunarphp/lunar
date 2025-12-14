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
        'cutoff' => [
            'label' => 'Horário limite',
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
