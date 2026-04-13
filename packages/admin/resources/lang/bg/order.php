<?php

return [

    'label' => 'Поръчка',

    'plural_label' => 'Поръчки',

    'breadcrumb' => [
        'manage' => 'Управление',
    ],

    'tabs' => [
        'all' => 'Всички',
    ],

    'transactions' => [
        'capture' => 'Каптурирано',
        'intent' => 'Очакващо',
        'refund' => 'Възстановено',
        'failed' => 'Неуспешно',
    ],

    'table' => [
        'status' => [
            'label' => 'Статус',
        ],
        'reference' => [
            'label' => 'Референция',
        ],
        'customer_reference' => [
            'label' => 'Референция на клиента',
        ],
        'customer' => [
            'label' => 'Клиент',
        ],
        'tags' => [
            'label' => 'Етикети',
        ],
        'postcode' => [
            'label' => 'Пощенски код',
        ],
        'email' => [
            'label' => 'Имейл',
            'copy_message' => 'Имейл адресът е копиран',
        ],
        'phone' => [
            'label' => 'Телефон',
        ],
        'total' => [
            'label' => 'Общо',
        ],
        'date' => [
            'label' => 'Дата',
        ],
        'new_customer' => [
            'label' => 'Тип клиент',
        ],
        'placed_after' => [
            'label' => 'Поръчки след',
        ],
        'placed_before' => [
            'label' => 'Поръчки преди',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Име',
            ],
            'last_name' => [
                'label' => 'Фамилия',
            ],
            'line_one' => [
                'label' => 'Адрес – ред 1',
            ],
            'line_two' => [
                'label' => 'Адрес – ред 2',
            ],
            'line_three' => [
                'label' => 'Адрес – ред 3',
            ],
            'company_name' => [
                'label' => 'Име на фирмата',
            ],
            'tax_identifier' => [
                'label' => 'Данъчен номер',
            ],
            'contact_phone' => [
                'label' => 'Телефон',
            ],
            'contact_email' => [
                'label' => 'Имейл адрес',
            ],
            'city' => [
                'label' => 'Град',
            ],
            'state' => [
                'label' => 'Област / Провинция',
            ],
            'postcode' => [
                'label' => 'Пощенски код',
            ],
            'country_id' => [
                'label' => 'Държава',
            ],
        ],

        'reference' => [
            'label' => 'Референция',
        ],
        'status' => [
            'label' => 'Статус',
        ],
        'transaction' => [
            'label' => 'Транзакция',
        ],
        'amount' => [
            'label' => 'Сума',

            'hint' => [
                'less_than_total' => 'Ще каптурирате сума, по-малка от общата стойност на транзакцията',
            ],
        ],

        'notes' => [
            'label' => 'Бележки',
        ],
        'confirm' => [
            'label' => 'Потвърди',

            'alert' => 'Изисква се потвърждение',

            'hint' => [
                'capture' => 'Моля, потвърдете, че искате да каптурирате това плащане',
                'refund' => 'Моля, потвърдете, че желаете да възстановите тази сума.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Бележки',
            'placeholder' => 'Няма бележки за тази поръчка',
        ],
        'delivery_instructions' => [
            'label' => 'Инструкции за доставка',
        ],
        'shipping_total' => [
            'label' => 'Общо за доставка',
        ],
        'paid' => [
            'label' => 'Платено',
        ],
        'refund' => [
            'label' => 'Възстановено',
        ],
        'unit_price' => [
            'label' => 'Единична цена',
        ],
        'quantity' => [
            'label' => 'Количество',
        ],
        'sub_total' => [
            'label' => 'Междинна сума',
        ],
        'discount_total' => [
            'label' => 'Общо отстъпки',
        ],
        'total' => [
            'label' => 'Общо',
        ],
        'current_stock_level' => [
            'message' => 'Текущо ниво на наличност: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'по време на поръчката: :count',
        ],
        'status' => [
            'label' => 'Статус',
        ],
        'reference' => [
            'label' => 'Референция',
        ],
        'customer_reference' => [
            'label' => 'Референция на клиента',
        ],
        'channel' => [
            'label' => 'Канал',
        ],
        'date_created' => [
            'label' => 'Дата на създаване',
        ],
        'date_placed' => [
            'label' => 'Дата на поръчка',
        ],
        'new_returning' => [
            'label' => 'Нов / Връщащ се',
        ],
        'new_customer' => [
            'label' => 'Нов клиент',
        ],
        'returning_customer' => [
            'label' => 'Връщащ се клиент',
        ],
        'shipping_address' => [
            'label' => 'Адрес за доставка',
        ],
        'billing_address' => [
            'label' => 'Адрес за фактуриране',
        ],
        'address_not_set' => [
            'label' => 'Адрес не е зададен',
        ],
        'billing_matches_shipping' => [
            'label' => 'Същият като адрес за доставка',
        ],
        'additional_info' => [
            'label' => 'Допълнителна информация',
        ],
        'no_additional_info' => [
            'label' => 'Няма допълнителна информация',
        ],
        'tags' => [
            'label' => 'Етикети',
        ],
        'timeline' => [
            'label' => 'Хронология',
        ],
        'transactions' => [
            'label' => 'Транзакции',
            'placeholder' => 'Няма транзакции',
        ],
        'alert' => [
            'requires_capture' => 'Тази поръчка все още изисква каптуриране на плащане.',
            'partially_refunded' => 'Тази поръчка е частично възстановена.',
            'refunded' => 'Тази поръчка е възстановена.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Актуализиране на статус',
            'notification' => 'Статусите на поръчките са актуализирани',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Нов статус',
            ],
            'additional_content' => [
                'label' => 'Допълнително съдържание',
            ],
            'additional_email_recipient' => [
                'label' => 'Допълнителен получател на имейл',
                'placeholder' => 'по избор',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Изтегляне на PDF',
            'notification' => 'Изтегляне на PDF на поръчката',
        ],
        'edit_address' => [
            'label' => 'Редактиране',

            'notification' => [
                'error' => 'Грешка',

                'billing_address' => [
                    'saved' => 'Адресът за фактуриране е запазен',
                ],

                'shipping_address' => [
                    'saved' => 'Адресът за доставка е запазен',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Редактиране',
            'form' => [
                'tags' => [
                    'label' => 'Етикети',
                    'helper_text' => 'Разделяйте етикетите с Enter, Tab или запетая (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Каптуриране на плащане',

            'notification' => [
                'error' => 'Възникна проблем при каптурирането',
                'success' => 'Каптурирането е успешно',
            ],
        ],
        'refund_payment' => [
            'label' => 'Възстановяване',

            'notification' => [
                'error' => 'Възникна проблем при възстановяването',
                'success' => 'Възстановяването е успешно',
            ],
        ],
    ],

];
