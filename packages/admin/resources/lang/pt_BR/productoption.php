<?php

return [

    'label' => 'Opção de Produto',

    'plural_label' => 'Opções de Produto',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'label' => [
            'label' => 'Rótulo',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'label' => [
            'label' => 'Rótulo',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Variantes do Produto Salvas',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Cancelar',
                ],
                'save-options' => [
                    'label' => 'Salvar Opções',
                ],
                'add-shared-option' => [
                    'label' => 'Adicionar Opção Compartilhada',
                    'form' => [
                        'product_option' => [
                            'label' => 'Opção de Produto',
                        ],
                        'no_shared_components' => [
                            'label' => 'Não há opções compartilhadas disponíveis.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Adicionar Opção',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Não há opções de produto configuradas',
                    'description' => 'Adicione uma opção de produto compartilhada ou restrita para começar a gerar algumas variantes.',
                ],
            ],
            'options-table' => [
                'title' => 'Opções de Produto',
                'configure-options' => [
                    'label' => 'Configurar Opções',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Opção',
                    ],
                    'values' => [
                        'label' => 'Valores',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Variantes do Produto',
                'actions' => [
                    'create' => [
                        'label' => 'Criar Variante',
                    ],
                    'edit' => [
                        'label' => 'Editar',
                    ],
                    'delete' => [
                        'label' => 'Excluir',
                    ],
                ],
                'empty' => [
                    'heading' => 'Nenhuma Variante Configurada',
                ],
                'table' => [
                    'new' => [
                        'label' => 'NOVO',
                    ],
                    'option' => [
                        'label' => 'Opção',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Preço',
                    ],
                    'stock' => [
                        'label' => 'Estoque',
                    ],
                ],
            ],
        ],
    ],

];
