<?php

return [
    'non_purchasable_item' => 'Model ":class" ne implementira sučelje purchasable.',
    'cart_line_id_mismatch' => 'Ova stavka košarice ne pripada ovoj košarici',
    'invalid_cart_line_quantity' => 'Očekivana količina je najmanje "1", pronađeno ":quantity".',
    'maximum_cart_line_quantity' => 'Količina ne smije premašiti :quantity.',
    'carts.invalid_action' => 'Akcija košarice nije valjana',
    'carts.shipping_missing' => 'Adresa za dostavu je obavezna',
    'carts.billing_missing' => 'Adresa za naplatu je obavezna',
    'carts.billing_incomplete' => 'Adresa za naplatu je nepotpuna',
    'carts.order_exists' => 'Narudžba za ovu košaricu već postoji',
    'carts.shipping_option_missing' => 'Nedostaje opcija dostave',
    'carts.line_unavailable' => 'Stavka u ovoj košarici više nije dostupna (:identifier)',
    'carts.line_not_purchasable' => ':identifier se ne može kupiti u ovom kanalu ili od strane vaše grupe kupaca',
    'missing_currency_price' => 'Ne postoji cijena za valutu ":currency"',
    'minimum_quantity' => 'Morate dodati najmanje :quantity stavki.',
    'quantity_increment' => 'Količina :quantity mora biti u koracima od :increment',
    'fieldtype_missing' => 'FieldType ":class" ne postoji',
    'invalid_fieldtype' => 'Klasa ":class" ne implementira sučelje FieldType.',
    'discounts.invalid_type' => 'Kolekcija smije sadržavati samo ":expected", pronađeno ":actual"',
    'disallow_multiple_cart_orders' => 'Košarica može imati samo jednu povezanu narudžbu.',
];
