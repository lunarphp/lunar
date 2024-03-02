<?php

return [
    'non_purchasable_item' => 'The ":class" model does not implement the purchasable interface.',
    'cart_line_id_mismatch' => 'This cart line does not belong to this cart',
    'invalid_cart_line_quantity' => 'Expected quantity to be at least "1", ":quantity" found.',
    'maximum_cart_line_quantity' => 'Quantity cannot exceed :quantity.',
    'carts.invalid_action' => 'The cart action was invalid',
    'carts.shipping_missing' => 'A shipping address is required',
    'carts.billing_missing' => 'A billing address is required',
    'carts.billing_incomplete' => 'The billing address is incomplete',
    'carts.order_exists' => 'An order for this cart already exists',
    'carts.shipping_option_missing' => 'Missing Shipping Option',
    'missing_currency_price' => 'No price for currency ":currency" exists',
    'fieldtype_missing' => 'FieldType ":class" does not exist',
    'invalid_fieldtype' => 'Class ":class" does not implement the FieldType interface.',
    'discounts.invalid_type' => 'Collection must only contain ":expected", found ":actual"',
    'disallow_multiple_cart_orders' => 'Carts can only have one order associated to them.',
];
