<?php

return [
    'customer_groups' => [
        'title' => 'Grupos de clientes',
        'actions' => [
            'attach' => [
                'label' => 'Anexar grupo de clientes',
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
                'label' => 'Data de início',
            ],
            'ends_at' => [
                'label' => 'Data de término',
            ],
            'visible' => [
                'label' => 'Visível',
            ],
            'purchasable' => [
                'label' => 'Disponível para compra',
            ],
        ],
        'table' => [
            'description' => 'Associe grupos de clientes a este :type para determinar sua disponibilidade.',
            'name' => [
                'label' => 'Nome',
            ],
            'enabled' => [
                'label' => 'Ativado',
            ],
            'starts_at' => [
                'label' => 'Data de início',
            ],
            'ends_at' => [
                'label' => 'Data de término',
            ],
            'visible' => [
                'label' => 'Visível',
            ],
            'purchasable' => [
                'label' => 'Disponível para compra',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Canais',
        'actions' => [
            'attach' => [
                'label' => 'Agendar outro canal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Ativado',
                'helper_text_false' => 'Este canal não será ativado mesmo que uma data de início esteja definida.',
            ],
            'starts_at' => [
                'label' => 'Data de início',
                'helper_text' => 'Deixe em branco para ficar disponível a partir de qualquer data.',
            ],
            'ends_at' => [
                'label' => 'Data de término',
                'helper_text' => 'Deixe em branco para ficar disponível indefinidamente.',
            ],
        ],
        'table' => [
            'description' => 'Defina quais canais estão ativados e agende a disponibilidade.',
            'name' => [
                'label' => 'Nome',
            ],
            'enabled' => [
                'label' => 'Ativado',
            ],
            'starts_at' => [
                'label' => 'Data de início',
            ],
            'ends_at' => [
                'label' => 'Data de término',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Mídia',
        'title_plural' => 'Mídia',
        'actions' => [
            'attach' => [
                'label' => 'Anexar mídia',
            ],
            'create' => [
                'label' => 'Criar mídia',
            ],
            'detach' => [
                'label' => 'Desanexar',
            ],
            'view' => [
                'label' => 'Visualizar',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nome',
            ],
            'media' => [
                'label' => 'Imagem',
            ],
            'primary' => [
                'label' => 'Principal',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Imagem',
            ],
            'file' => [
                'label' => 'Arquivo',
            ],
            'name' => [
                'label' => 'Nome',
            ],
            'primary' => [
                'label' => 'Principal',
            ],
        ],
        'all_media_attached' => 'Não há imagens de produtos disponíveis para anexar',
        'variant_description' => 'Anexe imagens de produto a esta variação',
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
        'title' => 'Preços por grupo de clientes',
        'title_plural' => 'Preços por grupo de clientes',
        'table' => [
            'heading' => 'Preços por grupo de clientes',
            'description' => 'Associe preços a grupos de clientes para determinar o preço do produto.',
            'empty_state' => [
                'label' => 'Não existem preços por grupo de clientes.',
                'description' => 'Crie um preço para grupo de clientes para começar.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Adicionar preço por grupo de clientes',
                    'modal' => [
                        'heading' => 'Criar preço por grupo de clientes',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Preço',
        'title_plural' => 'Preços',
        'tab_name' => 'Faixas de preço',
        'table' => [
            'heading' => 'Faixas de preço',
            'description' => 'Reduza o preço quando o cliente comprar em maiores quantidades.',
            'empty_state' => [
                'label' => 'Não existem faixas de preço.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Adicionar faixa de preço',
                ],
            ],
            'price' => [
                'label' => 'Preço',
            ],
            'customer_group' => [
                'label' => 'Grupo de clientes',
                'placeholder' => 'Todos os grupos de clientes',
            ],
            'min_quantity' => [
                'label' => 'Quantidade mínima',
            ],
            'currency' => [
                'label' => 'Moeda',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Preço',
                'helper_text' => 'Preço de compra, antes de descontos.',
            ],
            'customer_group_id' => [
                'label' => 'Grupo de clientes',
                'placeholder' => 'Todos os grupos de clientes',
                'helper_text' => 'Selecione a qual grupo de clientes aplicar este preço.',
            ],
            'min_quantity' => [
                'label' => 'Quantidade mínima',
                'helper_text' => 'Selecione a quantidade mínima para a qual este preço estará disponível.',
                'validation' => [
                    'unique' => 'Grupo de clientes e quantidade mínima devem ser únicos.',
                ],
            ],
            'currency_id' => [
                'label' => 'Moeda',
                'helper_text' => 'Selecione a moeda para este preço.',
            ],
            'compare_price' => [
                'label' => 'Preço de comparação',
                'helper_text' => 'O preço original (ou preço sugerido) para comparação com o preço de compra.',
            ],
            'basePrices' => [
                'title' => 'Preços',
                'form' => [
                    'price' => [
                        'label' => 'Preço',
                        'helper_text' => 'Preço de compra, antes de descontos.',
                        'sync_price' => 'Preço sincronizado com a moeda padrão.',
                    ],
                    'compare_price' => [
                        'label' => 'Preço de comparação',
                        'helper_text' => 'O preço original (ou preço sugerido) para comparação com o preço de compra.',
                    ],
                ],
                'tooltip' => 'Gerado automaticamente com base nas taxas de câmbio.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Percentual',
            ],
            'tax_class' => [
                'label' => 'Classe de imposto',
            ],
        ],
    ],
    'values' => [
        'title' => 'Valores',
        'table' => [
            'name' => [
                'label' => 'Nome',
            ],
            'position' => [
                'label' => 'Posição',
            ],
        ],
    ],

];
