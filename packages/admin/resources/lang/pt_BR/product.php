<?php

return [

    'label' => 'Produto',

    'plural_label' => 'Produtos',

    'status' => [
        'unpublished' => [
            'content' => 'Atualmente em status de rascunho, este produto está oculto em todos os canais e grupos de clientes.',
        ],
        'availability' => [
            'customer_groups' => 'Este produto está atualmente indisponível para todos os grupos de clientes.',
            'channels' => 'Este produto está atualmente indisponível para todos os canais.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'brand' => [
            'label' => 'Marca',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Estoque',
        ],
        'producttype' => [
            'label' => 'Tipo de Produto',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Atualizar Status',
            'heading' => 'Atualizar Status',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
        'brand' => [
            'label' => 'Marca',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Tipo de Produto',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Publicado',
                    'description' => 'Este produto estará disponível para todos os grupos de clientes e canais ativados',
                ],
                'draft' => [
                    'label' => 'Rascunho',
                    'description' => 'Este produto será oculto em todos os canais e grupos de clientes',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'collections' => [
            'label' => 'Coleções',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Disponibilidade',
        ],
        'media' => [
            'label' => 'Mídia',
        ],
        'identifiers' => [
            'label' => 'Identificadores do Produto',
        ],
        'inventory' => [
            'label' => 'Inventário',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Classe de Imposto',
                ],
                'tax_ref' => [
                    'label' => 'Referência de Imposto',
                    'helper_text' => 'Opcional, para integração com sistemas de terceiros.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Envio',
        ],
        'variants' => [
            'label' => 'Variantes',
        ],
        'collections' => [
            'label' => 'Coleções',
        ],
        'associations' => [
            'label' => 'Associações de Produto',
        ],
    ],

];
