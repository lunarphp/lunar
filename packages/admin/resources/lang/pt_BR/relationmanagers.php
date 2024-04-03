<?php

return [
    'customer_groups' => [
        'actions' => [
            'attach' => [
                'label' => 'Anexar Grupo de Clientes',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nome',
            ],
            'enabled' => [
                'label' => 'Ativado',
            ],
            'starts_at' => [
                'label' => 'Data de Início',
            ],
            'ends_at' => [
                'label' => 'Data de Término',
            ],
            'visible' => [
                'label' => 'Visível',
            ],
            'purchasable' => [
                'label' => 'Comprável',
            ],
        ],
        'table' => [
            'description' => 'Associe grupos de clientes a este produto para determinar sua disponibilidade.',
            'name' => [
                'label' => 'Nome',
            ],
            'enabled' => [
                'label' => 'Ativado',
            ],
            'starts_at' => [
                'label' => 'Data de Início',
            ],
            'ends_at' => [
                'label' => 'Data de Término',
            ],
            'visible' => [
                'label' => 'Visível',
            ],
            'purchasable' => [
                'label' => 'Comprável',
            ],
        ],
    ],
    'channels' => [
        'actions' => [
            'attach' => [
                'label' => 'Agendar outro Canal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Ativado',
                'helper_text_false' => 'Este canal não será ativado mesmo que uma data de início esteja presente.',
            ],
            'starts_at' => [
                'label' => 'Data de Início',
                'helper_text' => 'Deixe em branco para estar disponível a partir de qualquer data.',
            ],
            'ends_at' => [
                'label' => 'Data de Término',
                'helper_text' => 'Deixe em branco para estar disponível indefinidamente.',
            ],
        ],
        'table' => [
            'description' => 'Determine quais canais estão ativados e agende a disponibilidade.',
            'name' => [
                'label' => 'Nome',
            ],
            'enabled' => [
                'label' => 'Ativado',
            ],
            'starts_at' => [
                'label' => 'Data de Início',
            ],
            'ends_at' => [
                'label' => 'Data de Término',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'Criar URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Idioma',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Padrão',
            ],
            'language' => [
                'label' => 'Idioma',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Padrão',
            ],
            'language' => [
                'label' => 'Idioma',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Preços para Grupos de Clientes',
        'title_plural' => 'Preços para Grupos de Clientes',
        'table' => [
            'heading' => 'Preços para Grupos de Clientes',
            'description' => 'Associe preço a grupos de clientes para determinar o preço do produto.',
            'empty_state' => [
                'label' => 'Não existem preços para grupos de clientes.',
                'description' => 'Crie um preço para grupo de clientes para começar.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Adicionar Preço para Grupo de Clientes',
                    'modal' => [
                        'heading' => 'Criar Preço para Grupo de Clientes',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Preços',
        'title_plural' => 'Preços',
        'tab_name' => 'Quebras de Preço',
        'table' => [
            'heading' => 'Quebras de Preço',
            'description' => 'Reduza o preço quando um cliente comprar em quantidades maiores.',
            'empty_state' => [
                'label' => 'Não existem quebras de preço.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Adicionar Quebra de Preço',
                ],
            ],
            'price' => [
                'label' => 'Preço',
            ],
            'customer_group' => [
                'label' => 'Grupo de Clientes',
                'placeholder' => 'Todos os Grupos de Clientes',
            ],
            'min_quantity' => [
                'label' => 'Quantidade Mínima',
            ],
            'currency' => [
                'label' => 'Moeda',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Preço',
                'helper_text' => 'O preço de compra, antes dos descontos.',
            ],
            'customer_group_id' => [
                'label' => 'Grupo de Clientes',
                'placeholder' => 'Todos os Grupos de Clientes',
                'helper_text' => 'Selecione a qual grupo de clientes aplicar este preço.',
            ],
            'min_quantity' => [
                'label' => 'Quantidade Mínima',
                'helper_text' => 'Selecione a quantidade mínima para a qual este preço estará disponível.',
                'validation' => [
                    'unique' => 'O Grupo de Clientes e a Quantidade Mínima devem ser únicos.',
                ],
            ],
            'currency_id' => [
                'label' => 'Moeda',
                'helper_text' => 'Selecione a moeda para este preço.',
            ],
            'compare_price' => [
                'label' => 'Preço de Comparação',
                'helper_text' => 'O preço original ou PVP, para comparação com o preço de compra.',
            ],
            'basePrices' => [
                'title' => 'Preços',
                'form' => [
                    'price' => [
                        'label' => 'Preço',
                        'helper_text' => 'O preço de compra, antes dos descontos.',
                    ],
                    'compare_price' => [
                        'label' => 'Preço de Comparação',
                        'helper_text' => 'O preço original ou PVP, para comparação com o preço de compra.',
                    ],
                ],
                'tooltip' => 'Gerado automaticamente com base nas taxas de câmbio da moeda.',
            ],
        ],
    ],
];
