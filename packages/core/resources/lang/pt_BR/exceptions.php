<?php

return [
    'non_purchasable_item' => 'O modelo ":class" não implementa a interface de comprável.',
    'cart_line_id_mismatch' => 'Esta linha do carrinho não pertence a este carrinho',
    'invalid_cart_line_quantity' => 'Esperava-se uma quantidade de pelo menos "1", encontrou-se ":quantity".',
    'maximum_cart_line_quantity' => 'A quantidade não pode exceder :quantity.',
    'carts.shipping_missing' => 'Um endereço de entrega é obrigatório',
    'carts.billing_missing' => 'Um endereço de cobrança é obrigatório',
    'carts.billing_incomplete' => 'O endereço de cobrança está incompleto',
    'carts.order_exists' => 'Já existe um pedido para este carrinho',
    'carts.shipping_option_missing' => 'Opção de entrega ausente',
    'missing_currency_price' => 'Não há preço para a moeda ":currency"',
    'minimum_quantity' => 'Você deve adicionar no mínimo :quantity itens.',
    'quantity_increment' => 'A quantidade :quantity deve ser em incrementos de :increment',
    'fieldtype_missing' => 'O tipo de campo ":class" não existe',
    'invalid_fieldtype' => 'A classe ":class" não implementa a interface FieldType.',
    'discounts.invalid_type' => 'A coleção deve conter apenas ":expected", encontrou-se ":actual"',
    'disallow_multiple_cart_orders' => 'Os carrinhos só podem ter um pedido associado a eles.',
];
