<?php

return [
    'label' => 'Variante de Produto',
    'plural_label' => 'Variantes de Produto',
    'pages' => [
        'edit' => [
            'title' => 'Informações Básicas',
        ],
        'media' => [
            'title' => 'Mídia',
            'form' => [
                'no_selection' => [
                    'label' => 'Você não tem atualmente uma imagem selecionada para esta variante.',
                ],
                'no_media_available' => [
                    'label' => 'Atualmente não há mídia disponível neste produto.',
                ],
                'images' => [
                    'label' => 'Imagem Principal',
                    'helper_text' => 'Selecione a imagem do produto que representa esta variante.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identificadores',
        ],
        'inventory' => [
            'title' => 'Inventário',
        ],
        'shipping' => [
            'title' => 'Envio',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Número Global de Item Comercial (GTIN)',
        ],
        'mpn' => [
            'label' => 'Número da Peça do Fabricante (MPN)',
        ],
        'ean' => [
            'label' => 'Código de Produto Universal (UPC/EAN)',
        ],
        'stock' => [
            'label' => 'Em Estoque',
        ],
        'backorder' => [
            'label' => 'Em Pedidos Pendentes',
        ],
        'purchasable' => [
            'label' => 'Disponibilidade para Compra',
            'options' => [
                'always' => 'Sempre',
                'in_stock' => 'Em Estoque',
                'backorder' => 'Apenas em Pedidos Pendentes',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Quantidade por Unidade',
            'helper_text' => 'Quantos itens individuais compõem 1 unidade.',
        ],
        'min_quantity' => [
            'label' => 'Quantidade Mínima',
            'helper_text' => 'A quantidade mínima de uma variante de produto que pode ser comprada em uma única compra.',
        ],
        'quantity_increment' => [
            'label' => 'Incremento de Quantidade',
            'helper_text' => 'A variante de produto deve ser comprada em múltiplos dessa quantidade.',
        ],
        'tax_class_id' => [
            'label' => 'Classe de Imposto',
        ],
        'shippable' => [
            'label' => 'Permite Envio',
        ],
        'length_value' => [
            'label' => 'Comprimento',
        ],
        'length_unit' => [
            'label' => 'Unidade de Comprimento',
        ],
        'width_value' => [
            'label' => 'Largura',
        ],
        'width_unit' => [
            'label' => 'Unidade de Largura',
        ],
        'height_value' => [
            'label' => 'Altura',
        ],
        'height_unit' => [
            'label' => 'Unidade de Altura',
        ],
        'weight_value' => [
            'label' => 'Peso',
        ],
        'weight_unit' => [
            'label' => 'Unidade de Peso',
        ],
    ],
];
