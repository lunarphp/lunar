<?php

return [
    'plural_label' => 'Descontos',
    'label' => 'Desconto',
    'form' => [
        'conditions' => [
            'heading' => 'Condições',
        ],
        'buy_x_get_y' => [
            'heading' => 'Compre X e ganhe Y',
        ],
        'amount_off' => [
            'heading' => 'Valor de Desconto',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'starts_at' => [
            'label' => 'Data de Início',
        ],
        'ends_at' => [
            'label' => 'Data de Término',
        ],
        'priority' => [
            'label' => 'Prioridade',
            'helper_text' => 'Os descontos com maior prioridade serão aplicados primeiro.',
            'options' => [
                'low' => [
                    'label' => 'Baixa',
                ],
                'medium' => [
                    'label' => 'Média',
                ],
                'high' => [
                    'label' => 'Alta',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Parar outros descontos de aplicar depois deste',
        ],
        'coupon' => [
            'label' => 'Cupom',
            'helper_text' => 'Insira o cupom necessário para o desconto se aplicar, se deixado em branco, será aplicado automaticamente.',
        ],
        'max_uses' => [
            'label' => 'Máximo de Usos',
            'helper_text' => 'Deixe em branco para usos ilimitados.',
        ],
        'max_uses_per_user' => [
            'label' => 'Máximo de Usos por Usuário',
            'helper_text' => 'Deixe em branco para usos ilimitados.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Valor Mínimo no Carrinho',
        ],
        'min_qty' => [
            'label' => 'Quantidade do Produto',
            'helper_text' => 'Defina quantos produtos qualificadores são necessários para que o desconto se aplique.',
        ],
        'reward_qty' => [
            'label' => 'Quantidade de itens gratuitos',
            'helper_text' => 'Quantos de cada item são descontados.',
        ],
        'max_reward_qty' => [
            'label' => 'Quantidade máxima de recompensa',
            'helper_text' => 'A quantidade máxima de produtos que podem ser descontados, independentemente dos critérios.',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'status' => [
            'label' => 'Status',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Ativo',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'Pendente',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Expirado',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Agendado',
            ],
        ],
        'type' => [
            'label' => 'Tipo',
        ],
        'starts_at' => [
            'label' => 'Data de Início',
        ],
        'ends_at' => [
            'label' => 'Data de Término',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilidade',
        ],
        'limitations' => [
            'label' => 'Limitações',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Coleções',
            'description' => 'Selecione a quais coleções este desconto deve ser limitado.',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar Coleção',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitação',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusão',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitação',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusão',
                        ],
                    ],
                ],
            ],
        ],
        'brands' => [
            'title' => 'Marcas',
            'description' => 'Selecione a quais marcas este desconto deve ser limitado.',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar Marca',
                ],
            ],
            'table' => [
                'name' => [


 'label' => 'Nome',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitação',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusão',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitação',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusão',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Produtos',
            'description' => 'Selecione a quais produtos este desconto deve ser limitado.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar Produto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitação',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusão',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitação',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusão',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Recompensas do Produto',
            'description' => 'Selecione quais produtos serão descontados se existirem no carrinho e as condições acima forem atendidas.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar Produto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitação',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusão',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitação',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusão',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Condições do Produto',
            'description' => 'Selecione os produtos necessários para que o desconto se aplique.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar Produto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
                'type' => [
                    'label' => 'Tipo',
                    'limitation' => [
                        'label' => 'Limitação',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusão',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitação',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusão',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Variantes de Produto',
            'description' => 'Selecione quais variantes de produto este desconto deve ser limitado.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar Variante de Produto',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Opção(ões)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitação',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusão',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
