<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'A receber',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
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
        'continue_selling' => [
            'label' => 'Continuar vendendo sem estoque',
            'tooltip' => 'Quando ativado, esta variante pode ser pedida independentemente do estoque. Quando desativado, os clientes só podem comprar o que está disponível.',
        ],
        'sell_against_incoming' => [
            'label' => 'Vender com base no estoque a receber',
            'tooltip' => 'Conta o estoque a receber como disponível, para que os clientes possam pedir unidades que você está esperando.',
        ],
        'unit_quantity' => [
            'label' => 'Quantidade por unidade',
            'tooltip' => 'Quantos itens individuais formam 1 unidade.',
        ],
        'min_quantity' => [
            'label' => 'Quantidade mínima',
            'tooltip' => 'Quantidade mínima da variação que pode ser comprada em uma única compra.',
        ],
        'quantity_increment' => [
            'label' => 'Incremento de quantidade',
            'tooltip' => 'A variação deve ser comprada em múltiplos desta quantidade.',
        ],
        'tax_class_id' => [
            'label' => 'Classe de imposto',
        ],
        'shippable' => [
            'label' => 'Enviável',
        ],
        'fulfilment_methods' => [
            'label' => 'Métodos de fulfillment',
            'helper_text' => 'Como esta variante é atendida. Marcar um método físico, como envio ou retirada, a torna enviável. Não marque nenhum quando não houver nada a atender, como um download imediato.',
            'unavailable' => ':methods não está disponível no momento, então os clientes não podem escolhê-lo. Desmarque-o ou torne-o disponível novamente.',
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
