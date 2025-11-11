<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Associe grupos de clientes a este método de envio para determinar sua disponibilidade.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Taxas de envio',
        'actions' => [
            'create' => [
                'label' => 'Criar taxa de envio',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Todos os preços incluem imposto, o que será considerado ao calcular o gasto mínimo.',
            'prices_excl_tax' => 'Todos os preços excluem imposto; o gasto mínimo será baseado no subtotal do carrinho.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Método de envio',
            ],
            'price' => [
                'label' => 'Preço',
            ],
            'prices' => [
                'label' => 'Faixas de preço',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Grupo de clientes',
                        'placeholder' => 'Qualquer',
                    ],
                    'currency_id' => [
                        'label' => 'Moeda',
                    ],
                    'min_spend' => [
                        'label' => 'Gasto mín.',
                    ],
                    'min_weight' => [
                        'label' => 'Peso mín.',
                    ],
                    'price' => [
                        'label' => 'Preço',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Método de envio',
            ],
            'price' => [
                'label' => 'Preço',
            ],
            'price_breaks_count' => [
                'label' => 'Faixas de preço',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Exclusões de envio',
        'form' => [
            'purchasable' => [
                'label' => 'Produto',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Adicionar lista de exclusões de envio',
            ],
            'attach' => [
                'label' => 'Adicionar lista de exclusões',
            ],
            'detach' => [
                'label' => 'Remover',
            ],
        ],
    ],
];
