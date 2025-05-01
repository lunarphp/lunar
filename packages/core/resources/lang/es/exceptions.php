<?php

return [
    'non_purchasable_item' => 'El modelo ":class" no implementa la interfaz comprable.',
    'cart_line_id_mismatch' => 'Esta línea del carrito no pertenece a este carrito.',
    'invalid_cart_line_quantity' => 'Se esperaba que la cantidad fuera al menos "1", se encontró ":quantity".',
    'maximum_cart_line_quantity' => 'La cantidad no puede exceder :quantity.',
    'carts.invalid_action' => 'La acción del carrito no es válida.',
    'carts.shipping_missing' => 'Se requiere una dirección de envío.',
    'carts.billing_missing' => 'Se requiere una dirección de facturación.',
    'carts.billing_incomplete' => 'La dirección de facturación está incompleta.',
    'carts.order_exists' => 'Ya existe un pedido para este carrito.',
    'carts.shipping_option_missing' => 'Opción de envío faltante.',
    'missing_currency_price' => 'No existe un precio para la moneda ":currency".',
    'minimum_quantity' => 'Debes agregar un mínimo de :quantity artículos.',
    'quantity_increment' => 'La cantidad :quantity debe ser en incrementos de :increment.',
    'fieldtype_missing' => 'El FieldType ":class" no existe.',
    'invalid_fieldtype' => 'La clase ":class" no implementa la interfaz FieldType.',
    'discounts.invalid_type' => 'La colección solo debe contener ":expected", se encontró ":actual".',
    'disallow_multiple_cart_orders' => 'Los carritos solo pueden tener un pedido asociado.',
];
