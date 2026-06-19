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
        'heading' => 'Fulfilments',
        'unreferenced' => 'Fulfilment #:id',
        'on_hold' => 'On hold',
        'empty' => 'No fulfilments yet.',
        'columns' => [
            'reference' => 'Reference',
            'state' => 'State',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Shipped at',
            'handed_over' => [
                'shipping' => 'Shipped at',
                'collection' => 'Collected at',
                'digital' => 'Provisioned at',
            ],
            'handed_over_default' => 'Fulfilled at',
        ],
        'actions' => [
            'more' => 'More actions',
            'notify' => 'Notify customer',
            'add_tracking' => [
                'label' => 'Add tracking',
                'modal_heading' => 'Add tracking',
                'notification' => [
                    'success' => 'Tracking added.',
                    'error' => 'Could not add tracking.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Remove tracking',
                'notification' => [
                    'success' => 'Tracking removed.',
                    'error' => 'Could not remove tracking.',
                ],
            ],
            'create' => [
                'label' => 'Create fulfilment',
                'modal_heading' => 'Create fulfilment',
                'empty' => 'Every line is already fulfilled.',
                'notification' => [
                    'success' => 'Fulfilment created.',
                    'error' => 'Could not create fulfilment.',
                ],
            ],
            'ship' => [
                'label' => 'Mark shipped',
                'modal_heading' => 'Mark fulfilment as shipped',
                'notification' => [
                    'success' => 'Fulfilment marked as shipped.',
                    'error' => 'Could not ship fulfilment.',
                ],
            ],
            'fulfil' => [
                'label' => 'Mark fulfilled',
                'modal_heading' => 'Mark fulfilment as fulfilled',
                'labels' => [
                    'collection' => 'Mark collected',
                ],
                'notification' => [
                    'success' => 'Fulfilment marked as fulfilled.',
                    'error' => 'Could not fulfil fulfilment.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancel fulfilment',
                'modal_heading' => 'Cancel fulfilment',
                'description' => 'This returns the fulfilment to pending so it can be progressed again. Any shipment details are cleared.',
                'notification' => [
                    'success' => 'Fulfilment cancelled.',
                    'error' => 'Could not cancel fulfilment.',
                ],
            ],
            'change_location' => [
                'label' => 'Change location',
                'modal_heading' => 'Change fulfilment location',
                'field' => 'Location',
                'notification' => [
                    'success' => 'Fulfilment location updated.',
                    'error' => 'Could not change the fulfilment location.',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'notification' => [
                    'success' => 'Fulfilment returned.',
                    'error' => 'Could not return fulfilment.',
                ],
            ],
            'update_status' => [
                'label' => 'Update status',
            ],
            'transition' => [
                'modal_heading' => 'Mark fulfilment as :status?',
                'notification' => [
                    'success' => 'Fulfilment status updated.',
                    'error' => 'Could not update the fulfilment status.',
                ],
            ],
            'undo_return' => [
                'label' => 'Undo return',
                'notification' => [
                    'success' => 'Return undone.',
                    'error' => 'Could not undo the return.',
                ],
            ],
            'hold' => [
                'label' => 'Hold fulfilment',
                'modal_heading' => 'Hold fulfilment',
                'reason' => 'Reason',
                'note' => 'Note',
                'notification' => [
                    'success' => 'Fulfilment placed on hold.',
                    'error' => 'Could not hold the fulfilment.',
                ],
            ],
            'release' => [
                'label' => 'Release hold',
                'notification' => [
                    'success' => 'Fulfilment released.',
                    'error' => 'Could not release the fulfilment.',
                ],
            ],
            'split' => [
                'label' => 'Split',
                'confirm' => 'Split fulfilment',
                'cancel' => 'Cancel',
                'empty' => 'Select a quantity to split out.',
                'modal_heading' => 'Split fulfilment',
                'notification' => [
                    'success' => 'Fulfilment split.',
                    'error' => 'Could not split fulfilment.',
                ],
            ],
            'merge' => [
                'label' => 'Merge',
                'confirm' => 'Merge fulfilment',
                'cancel' => 'Cancel',
                'modal_heading' => 'Merge fulfilment',
                'description' => 'Select the items you would like to merge.',
                'target' => 'Merge with',
                'empty' => 'Select items and a destination to merge.',
                'notification' => [
                    'success' => 'Fulfilments merged.',
                    'error' => 'Could not merge fulfilments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantity',
            'tracking' => 'Tracking',
            'tracking_item' => 'Tracking #:number',
            'unit_price' => 'Unit Price',
            'sub_total' => 'Sub Total',
            'discount_total' => 'Discount Total',
            'total' => 'Total',
            'stock_level' => 'Current Stock Level: :count',
            'of' => 'of :count',
            'outstanding' => 'Outstanding: :count',
            'tracking_number' => 'Tracking number',
            'tracking_url' => 'Tracking URL',
            'carrier' => 'Carrier',
            'carrier_custom' => 'Custom / other',
            'tracking_url_help' => 'Only needed for carriers without an automatic tracking link.',
            'shipping_method' => 'Shipping method',
            'move_quantity' => 'Quantity to move out',
        ],
    ],

    'other_items' => [
        'heading' => 'Other items',
    ],
];
