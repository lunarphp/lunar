<?php

return [
    'shipping_discount' => [
        'name' => 'Preço do envio',
        'form' => [
            'methods' => [
                'label' => 'Métodos de envio',
                'add_label' => 'Adicionar regra',
            ],
            'shipping_method_id' => [
                'label' => 'Método de envio',
                'placeholder' => 'Qualquer método de envio',
            ],
            'type' => [
                'label' => 'Tipo de desconto',
                'options' => [
                    'fixed' => 'Preço fixo',
                    'percentage' => 'Percentual de desconto',
                ],
            ],
            'percentage' => [
                'label' => 'Percentual de desconto (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Regras',
            'rules_description' => 'Cada regra se aplica a um método de envio, ou a todos os métodos quando nenhum é escolhido. Um método específico prevalece sobre a regra geral.',
            'add_rule' => 'Adicionar regra',
            'remove_rule' => 'Remover regra',
            'no_rules' => 'Nenhuma regra ainda. Adicione uma para alterar o custo do envio.',
            'method' => 'Método de envio',
            'method_any' => 'Qualquer método de envio',
            'type' => 'Efeito',
            'type_fixed' => 'Definir o preço do envio',
            'type_percentage' => 'Aplicar um percentual de desconto',
            'percentage' => 'Percentual de desconto (%)',
            'prices' => 'O preço do envio passa a ser',
            'prices_hint' => 'O cliente paga este valor pelo envio. Deixe uma moeda em branco para manter seu preço inalterado; informe 0 para envio grátis.',
        ],
        'summary_free' => 'Envio grátis',
        'summary_percentage' => ':percentage% de desconto no envio',
        'summary_from' => 'Envio a partir de :amount',
    ],
];
