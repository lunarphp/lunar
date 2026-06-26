<?php

return [
    'label' => 'Variação de produto',
    'plural_label' => 'Variações de produto',
    'pages' => [
        'edit' => [
            'title' => 'Informações básicas',
        ],
        'media' => [
            'title' => 'Mídia',
            'form' => [
                'no_selection' => [
                    'label' => 'Você não possui uma imagem selecionada para esta variação.',
                ],
                'no_media_available' => [
                    'label' => 'Não há mídia disponível neste produto.',
                ],
                'images' => [
                    'label' => 'Imagem principal',
                    'helper_text' => 'Selecione a imagem do produto que representa esta variação.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identificadores',
        ],
        'inventory' => [
            'title' => 'Estoque',
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
            'label' => 'GTIN (Global Trade Item Number)',
        ],
        'mpn' => [
            'label' => 'MPN (Manufacturer Part Number)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'tooltip' => 'Units on hand at your default location. Changing this records a stock adjustment.',
            'label' => 'Em estoque',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'Sob encomenda',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'Sempre',
                'in_stock' => 'Em estoque',
                'in_stock_or_on_backorder' => 'Em estoque ou sob encomenda',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Quantidade por unidade',
            'helper_text' => 'Quantos itens individuais formam 1 unidade.',
        ],
        'min_quantity' => [
            'label' => 'Quantidade mínima',
            'helper_text' => 'Quantidade mínima da variação que pode ser comprada em uma única compra.',
        ],
        'quantity_increment' => [
            'label' => 'Incremento de quantidade',
            'helper_text' => 'A variação deve ser comprada em múltiplos desta quantidade.',
        ],
        'tax_class_id' => [
            'label' => 'Classe de imposto',
        ],
        'shippable' => [
            'label' => 'Enviável',
        ],
        'length_value' => [
            'label' => 'Comprimento',
        ],
        'length_unit' => [
            'label' => 'Unidade de comprimento',
        ],
        'width_value' => [
            'label' => 'Largura',
        ],
        'width_unit' => [
            'label' => 'Unidade de largura',
        ],
        'height_value' => [
            'label' => 'Altura',
        ],
        'height_unit' => [
            'label' => 'Unidade de altura',
        ],
        'weight_value' => [
            'label' => 'Peso',
        ],
        'weight_unit' => [
            'label' => 'Unidade de peso',
        ],
    ],
];
