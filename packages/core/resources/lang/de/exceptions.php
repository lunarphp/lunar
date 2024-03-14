<?php

return [
    'non_purchasable_item' => 'Das Model ":class" implementiert nicht das "Bestellbar (purchasable)" Interface.',
    'cart_line_id_mismatch' => 'Die Position gehört nicht zu diesem Warenkorb.',
    'invalid_cart_line_quantity' => 'Die Bestellmenge muss mindestens "1" sein, angegeben wurden ":quantity".',
    'maximum_cart_line_quantity' => 'Die Bestellmenge darf nicht mehr als ":quantity" betragen.',
    'carts.shipping_missing' => 'Eine Lieferadresse ist erforderlich',
    'carts.billing_missing' => 'Eine Rechnungsadresse ist erforderlich',
    'carts.billing_incomplete' => 'Die Rechnungsadresse ist unvollständig',
    'carts.order_exists' => 'Für diesen Warenkorb existiert bereits eine Bestellung',
    'carts.shipping_option_missing' => 'Eine gültige Versandart fehlt',
    'missing_currency_price' => 'Es existiert kein Preis für die Währung ":currency"',
    'fieldtype_missing' => 'Der FeldType ":class" existiert nicht',
    'invalid_fieldtype' => 'Die Klasse ":class" implementiert nicht das Feldtyp-Interface.',
    'discounts.invalid_type' => 'Die Liste der Rabatte darf nur ":expected" enthalten, gefunden wurde ":actual"',
    'disallow_multiple_cart_orders' => 'Ein Warenkorb kann nur zu einer Bestellung gehören.',
];
