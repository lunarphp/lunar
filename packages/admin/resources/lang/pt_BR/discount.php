<?php

return [
    'plural_label' => 'Descontos',
    'label' => 'Desconto',
    'form' => [
        'conditions' => [
            'heading' => 'Condições',
        ],
        'buy_x_get_y' => [
            'heading' => 'Compre X, leve Y',
        ],
        'amount_off' => [
            'heading' => 'Valor de desconto',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'starts_at' => [
            'label' => 'Data de início',
        ],
        'ends_at' => [
            'label' => 'Data de término',
        ],
        'priority' => [
            'label' => 'Prioridade',
            'helper_text' => 'Descontos com maior prioridade serão aplicados primeiro.',
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
            'label' => 'Interromper outros descontos após este',
        ],
        'coupon' => [
            'label' => 'Cupom',
            'helper_text' => 'Informe o cupom necessário para aplicar o desconto; se deixado em branco, será aplicado automaticamente.',
        ],
        'max_uses' => [
            'label' => 'Máximo de usos',
            'helper_text' => 'Deixe em branco para usos ilimitados.',
        ],
        'max_uses_per_user' => [
            'label' => 'Máximo de usos por usuário',
            'helper_text' => 'Deixe em branco para usos ilimitados.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Valor mínimo do carrinho',
        ],
        'min_qty' => [
            'label' => 'Quantidade de produtos',
            'helper_text' => 'Defina quantos produtos qualificados são necessários para aplicar o desconto.',
        ],
        'reward_qty' => [
            'label' => 'Qtd. de itens grátis',
            'helper_text' => 'Quantos de cada item serão descontados.',
        ],
        'max_reward_qty' => [
            'label' => 'Quantidade máxima de recompensa',
            'helper_text' => 'Quantidade máxima de produtos que podem ser descontados, independentemente do critério.',
        ],
        'automatic_rewards' => [
            'label' => 'Adicionar recompensas automaticamente',
            'helper_text' => 'Ative para adicionar produtos de recompensa quando não estiverem no carrinho.',
        ],
        'fixed_value' => [
            'label' => 'Valor fixo',
        ],
        'percentage' => [
            'label' => 'Percentual',
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
            'label' => 'Data de início',
        ],
        'ends_at' => [
            'label' => 'Data de término',
        ],
        'created_at' => [
            'label' => 'Criado em',
        ],
        'coupon' => [
            'label' => 'Cupom',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilidade',
        ],
        'edit' => [
            'title' => 'Informações básicas',
        ],
        'limitations' => [
            'label' => 'Limitações',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Coleções',
            'description' => 'Selecione a quais coleções este desconto deve se limitar.',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar coleção',
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
        'customers' => [
            'title' => 'Clientes',
            'description' => 'Selecione a quais clientes este desconto deve se limitar.',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar cliente',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nome',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Marcas',
            'description' => 'Selecione a quais marcas este desconto deve se limitar.',
            'actions' => [
                'attach' => [
                    'label' => 'Anexar marca',
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
            'description' => 'Selecione a quais produtos este desconto deve se limitar.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar produto',
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
            'title' => 'Recompensas',
            'description' => 'Selecione quais produtos serão descontados se estiverem no carrinho e as condições acima forem atendidas.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar recompensa',
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
            'title' => 'Condições',
            'description' => 'Selecione as condições necessárias para aplicar o desconto.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar condição',
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
            'title' => 'Variações de produto',
            'description' => 'Selecione a quais variações de produto este desconto deve se limitar.',
            'actions' => [
                'attach' => [
                    'label' => 'Adicionar variação de produto',
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
