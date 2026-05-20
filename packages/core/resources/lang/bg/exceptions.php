<?php

return [
    'non_purchasable_item' => 'Моделът ":class" не имплементира интерфейса за закупуване.',
    'cart_line_id_mismatch' => 'Този ред от количката не принадлежи към тази количка',
    'invalid_cart_line_quantity' => 'Очакваното количество е минимум "1", намерено ":quantity".',
    'maximum_cart_line_quantity' => 'Количество не може да надвишава :quantity.',
    'carts.invalid_action' => 'Действието върху количката е невалидно',
    'carts.shipping_missing' => 'Необходим е адрес за доставка',
    'carts.billing_missing' => 'Необходим е адрес за фактуриране',
    'carts.billing_incomplete' => 'Адресът за фактуриране е непълен',
    'carts.order_exists' => 'Поръчка за тази количка вече съществува',
    'carts.shipping_option_missing' => 'Липсва опция за доставка',
    'carts.line_unavailable' => 'Артикул в тази количка вече не е наличен (:identifier)',
    'carts.line_not_purchasable' => ':identifier не може да бъде закупен в този канал или от вашата клиентска група',
    'missing_currency_price' => 'Няма цена за валута ":currency"',
    'minimum_quantity' => 'Трябва да добавите минимум :quantity артикула.',
    'quantity_increment' => 'Количество :quantity трябва да бъде в стъпки от :increment',
    'fieldtype_missing' => 'FieldType ":class" не съществува',
    'invalid_fieldtype' => 'Класът ":class" не имплементира интерфейса FieldType.',
    'discounts.invalid_type' => 'Колекцията трябва да съдържа само ":expected", намерено ":actual"',
    'disallow_multiple_cart_orders' => 'Количките могат да имат само една свързана поръчка.',
];
