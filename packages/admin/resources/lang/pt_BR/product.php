<?php

return [

    'label' => 'Produto',

    'plural_label' => 'Produtos',

    'tabs' => [
        'all' => 'Todos',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Atualmente em rascunho, este produto está indisponível em todos os canais e grupos de clientes.',
        ],
        'availability' => [
            'customer_groups' => 'Este produto está indisponível para todos os grupos de clientes.',
            'channels' => 'Este produto está indisponível para todos os canais.',
            'hidden_from_guests' => 'Os visitantes atualmente não conseguem ver nem comprar este produto. O grupo de clientes padrão não está habilitado ou visível para ele.',
            'no_default_customer_group' => 'Nenhum grupo de clientes padrão foi definido, então a visibilidade para visitantes não pode ser controlada aqui. Marque um grupo de clientes como padrão para gerenciar o acesso de visitantes.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
                'deleted' => 'Excluído',
                'draft' => 'Rascunho',
                'published' => 'Publicado',
            ],
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
            'label' => 'Tipo de produto',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Atualizar status',
            'heading' => 'Atualizar status',
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
            'label' => 'Tipo de produto',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Publicado',
                    'description' => 'Este produto ficará disponível em todos os canais e grupos de clientes habilitados',
                ],
                'draft' => [
                    'label' => 'Rascunho',
                    'description' => 'Este produto ficará oculto em todos os canais e grupos de clientes',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tags',
            'helper_text' => 'Separe tags pressionando Enter, Tab ou vírgula (,)',
        ],
        'collections' => [
            'label' => 'Coleções',
            'select_collection' => 'Selecione uma coleção',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Disponibilidade',
        ],
        'edit' => [
            'title' => 'Informações básicas',
        ],
        'identifiers' => [
            'label' => 'Identificadores do produto',
        ],
        'inventory' => [
            'label' => 'Estoque',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Classe de imposto',
                ],
                'tax_ref' => [
                    'label' => 'Referência de imposto',
                    'helper_text' => 'Opcional, para integração com sistemas de terceiros.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Envio',
        ],
        'variants' => [
            'label' => 'Variações',
        ],
        'collections' => [
            'label' => 'Coleções',
            'select_collection' => 'Selecione uma coleção',
        ],
        'associations' => [
            'label' => 'Associações de produto',
        ],
    ],

];
