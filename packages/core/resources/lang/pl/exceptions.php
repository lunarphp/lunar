<?php

return [
    'non_purchasable_item' => 'Model ":class" nie implementuje interfejsu zakupowego (purchasable interface).',
    'cart_line_id_mismatch' => 'Ta linia koszyka nie należy do tego koszyka',
    'invalid_cart_line_quantity' => 'Oczekiwano, że ilość wyniesie co najmniej "1", znaleziono ":quantity".',
    'maximum_cart_line_quantity' => 'Ilość nie może przekraczać :quantity.',
    'carts.invalid_action' => 'Akcja koszyka była nieprawidłowa',
    'carts.shipping_missing' => 'Wymagany jest adres dostawy',
    'carts.billing_missing' => 'Wymagany jest adres rozliczeniowy',
    'carts.billing_incomplete' => 'Adres rozliczeniowy jest niekompletny',
    'carts.order_exists' => 'Zamówienie dla tego koszyka już istnieje',
    'carts.shipping_option_missing' => 'Brak opcji wysyłki',
    'missing_currency_price' => 'Nie istnieje cena dla waluty ":currency"',
    'minimum_quantity' => 'Musisz dodać co najmniej :quantity elementów.',
    'quantity_increment' => 'Ilość :quantity musi być wielokrotnością :increment',
    'fieldtype_missing' => 'FieldType ":class" nie istnieje',
    'invalid_fieldtype' => 'Klasa ":class" nie implementuje interfejsu FieldType.',
    'discounts.invalid_type' => 'Kolekcja musi zawierać tylko ":expected", znaleziono ":actual"',
    'disallow_multiple_cart_orders' => 'Koszyki mogą mieć tylko jedno zamówienie powiązane z nimi.',
];
