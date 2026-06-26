<?php

return [
    'label' => 'Поръчка',
    'plural_label' => 'Поръчки',
    'breadcrumb' => [
        'manage' => 'Управление',
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
            'label' => 'Order',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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

    'fulfilments' => [
        'heading' => 'Изпълнения',
        'unreferenced' => 'Изпълнение #:id',
        'on_hold' => 'На изчакване',
        'empty' => 'Все още няма изпълнения.',
        'columns' => [
            'reference' => 'Референция',
            'state' => 'Състояние',
            'items' => 'Артикули',
            'tracking' => 'Проследяване',
            'shipped_at' => 'Изпратено на',
            'handed_over' => [
                'shipping' => 'Изпратено на',
                'collection' => 'Получено на',
                'digital' => 'Предоставено на',
            ],
            'handed_over_default' => 'Изпълнено на',
        ],
        'actions' => [
            'more' => 'Още действия',
            'notify' => 'Уведоми клиента',
            'add_tracking' => [
                'label' => 'Добави проследяване',
                'modal_heading' => 'Добави проследяване',
                'notification' => [
                    'success' => 'Проследяването е добавено.',
                    'error' => 'Проследяването не може да бъде добавено.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Премахни проследяване',
                'notification' => [
                    'success' => 'Проследяването е премахнато.',
                    'error' => 'Проследяването не може да бъде премахнато.',
                ],
            ],
            'create' => [
                'label' => 'Създай изпълнение',
                'modal_heading' => 'Създай изпълнение',
                'empty' => 'Всеки ред вече е изпълнен.',
                'notification' => [
                    'success' => 'Изпълнението е създадено.',
                    'error' => 'Изпълнението не може да бъде създадено.',
                ],
            ],
            'ship' => [
                'label' => 'Отбележи като изпратено',
                'modal_heading' => 'Отбележи изпълнението като изпратено',
                'notification' => [
                    'success' => 'Изпълнението е отбелязано като изпратено.',
                    'error' => 'Изпълнението не може да бъде изпратено.',
                ],
            ],
            'fulfil' => [
                'label' => 'Отбележи като изпълнено',
                'modal_heading' => 'Отбележи изпълнението като изпълнено',
                'labels' => [
                    'collection' => 'Отбележи като получено',
                ],
                'notification' => [
                    'success' => 'Изпълнението е отбелязано като изпълнено.',
                    'error' => 'Изпълнението не може да бъде изпълнено.',
                ],
            ],
            'cancel' => [
                'label' => 'Отмени изпълнение',
                'modal_heading' => 'Отмени изпълнение',
                'description' => 'Това връща изпълнението в изчакващо състояние, така че да може да бъде придвижено отново. Всички данни за пратката се изчистват.',
                'notification' => [
                    'success' => 'Изпълнението е отменено.',
                    'error' => 'Изпълнението не може да бъде отменено.',
                ],
            ],
            'change_location' => [
                'label' => 'Промени локация',
                'modal_heading' => 'Промени локацията на изпълнението',
                'field' => 'Локация',
                'notification' => [
                    'success' => 'Локацията на изпълнението е обновена.',
                    'error' => 'Локацията на изпълнението не може да бъде променена.',
                ],
            ],
            'return' => [
                'label' => 'Върни',
                'notification' => [
                    'success' => 'Изпълнението е върнато.',
                    'error' => 'Изпълнението не може да бъде върнато.',
                ],
            ],
            'update_status' => [
                'label' => 'Обнови статуса',
            ],
            'transition' => [
                'modal_heading' => 'Да се отбележи ли изпълнението като :status?',
                'notification' => [
                    'success' => 'Статусът на изпълнението е обновен.',
                    'error' => 'Статусът на изпълнението не може да бъде обновен.',
                ],
            ],
            'undo_return' => [
                'label' => 'Отмени връщането',
                'notification' => [
                    'success' => 'Връщането е отменено.',
                    'error' => 'Връщането не може да бъде отменено.',
                ],
            ],
            'hold' => [
                'label' => 'Постави на изчакване',
                'modal_heading' => 'Постави изпълнението на изчакване',
                'reason' => 'Причина',
                'note' => 'Бележка',
                'notification' => [
                    'success' => 'Изпълнението е поставено на изчакване.',
                    'error' => 'Изпълнението не може да бъде поставено на изчакване.',
                ],
            ],
            'release' => [
                'label' => 'Освободи от изчакване',
                'notification' => [
                    'success' => 'Изпълнението е освободено.',
                    'error' => 'Изпълнението не може да бъде освободено.',
                ],
            ],
            'split' => [
                'label' => 'Раздели',
                'confirm' => 'Раздели изпълнение',
                'cancel' => 'Отказ',
                'empty' => 'Изберете количество за разделяне.',
                'modal_heading' => 'Раздели изпълнение',
                'notification' => [
                    'success' => 'Изпълнението е разделено.',
                    'error' => 'Изпълнението не може да бъде разделено.',
                ],
            ],
            'merge' => [
                'label' => 'Обедини',
                'confirm' => 'Обедини изпълнение',
                'cancel' => 'Отказ',
                'modal_heading' => 'Обедини изпълнение',
                'description' => 'Изберете артикулите, които искате да обедините.',
                'target' => 'Обедини с',
                'empty' => 'Изберете артикули и местоназначение за обединяване.',
                'notification' => [
                    'success' => 'Изпълненията са обединени.',
                    'error' => 'Изпълненията не могат да бъдат обединени.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Количество',
            'tracking' => 'Проследяване',
            'tracking_item' => 'Проследяване #:number',
            'unit_price' => 'Единична цена',
            'sub_total' => 'Междинна сума',
            'discount_total' => 'Обща отстъпка',
            'total' => 'Общо',
            'stock_level' => 'Текуща наличност: :count',
            'of' => 'от :count',
            'outstanding' => 'Оставащи: :count',
            'tracking_number' => 'Номер за проследяване',
            'tracking_url' => 'URL за проследяване',
            'carrier' => 'Куриер',
            'carrier_custom' => 'По избор / друг',
            'tracking_url_help' => 'Необходимо е само за куриери без автоматична връзка за проследяване.',
            'shipping_method' => 'Метод на доставка',
            'move_quantity' => 'Количество за преместване',
        ],
    ],

    'other_items' => [
        'heading' => 'Други артикули',
    ],
];
