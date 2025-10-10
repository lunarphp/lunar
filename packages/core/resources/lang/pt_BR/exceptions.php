<?php

return [
    'non_purchasable_item' => 'O modelo ":class" não implementa a interface de compra (purchasable).',
    'cart_line_id_mismatch' => 'Esta linha de carrinho não pertence a este carrinho',
    'invalid_cart_line_quantity' => 'Quantidade esperada de pelo menos "1"; encontrado ":quantity".',
    'maximum_cart_line_quantity' => 'A quantidade não pode exceder :quantity.',
    'carts.invalid_action' => 'A ação do carrinho é inválida',
    'carts.shipping_missing' => 'É necessário um endereço de entrega',
    'carts.billing_missing' => 'É necessário um endereço de cobrança',
    'carts.billing_incomplete' => 'O endereço de cobrança está incompleto',
    'carts.order_exists' => 'Já existe um pedido para este carrinho',
    'carts.shipping_option_missing' => 'Opção de envio ausente',
    'missing_currency_price' => 'Não existe preço para a moeda ":currency"',
    'minimum_quantity' => 'Você deve adicionar no mínimo :quantity itens.',
    'quantity_increment' => 'A quantidade :quantity deve estar em incrementos de :increment',
    'fieldtype_missing' => 'FieldType ":class" não existe',
    'invalid_fieldtype' => 'A classe ":class" não implementa a interface FieldType.',
    'discounts.invalid_type' => 'A coleção deve conter apenas ":expected"; encontrado ":actual"',
    'disallow_multiple_cart_orders' => 'Carrinhos só podem ter um pedido associado a eles.',
];
