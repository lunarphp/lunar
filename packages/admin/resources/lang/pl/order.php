<?php

return [
    'label' => 'Zamówienie',
    'plural_label' => 'Zamówienia',
    'breadcrumb' => [
        'manage' => 'Zarządzanie zamówieniami',
    ],
    'transactions' => [
        'capture' => 'Przechwycona',
        'intent' => 'Rozpoczęta',
        'refund' => 'Zwrócona',
        'failed' => 'Nieudana',
    ],
    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Numer zamówienia',
        ],
        'customer_reference' => [
            'label' => 'Numer klienta',
        ],
        'customer' => [
            'label' => 'Klient',
        ],
        'tags' => [
            'label' => 'Tagi',
        ],
        'postcode' => [
            'label' => 'Kod pocztowy',
        ],
        'email' => [
            'label' => 'Email',
            'copy_message' => 'Adres email skopiowany',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Suma',
        ],
        'date' => [
            'label' => 'Data',
        ],
        'new_customer' => [
            'label' => 'Typ klienta',
        ],
        'placed_after' => [
            'label' => 'Złożone po',
        ],
        'placed_before' => [
            'label' => 'Złożone przed',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Imię',
            ],
            'last_name' => [
                'label' => 'Nazwisko',
            ],
            'line_one' => [
                'label' => 'Adres',
            ],
            'line_two' => [
                'label' => 'Adres cd.',
            ],
            'line_three' => [
                'label' => 'Adres cd.',
            ],
            'company_name' => [
                'label' => 'Nazwa firmy',
            ],
            'tax_identifier' => [
                'label' => 'Tax Identifier',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'Email',
            ],
            'city' => [
                'label' => 'Miasto',
            ],
            'state' => [
                'label' => 'Województwo',
            ],
            'postcode' => [
                'label' => 'Kod pocztowy',
            ],
            'country_id' => [
                'label' => 'Kraj',
            ],
        ],
        'reference' => [
            'label' => 'Numer zamówienia',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Transakcja',
        ],
        'amount' => [
            'label' => 'Kwota',
            'hint' => [
                'less_than_total' => 'Próbujesz pobrać kwotę mniejszą niż całkowita wartość transakcji.',
            ],
        ],
        'notes' => [
            'label' => 'Notatki',
        ],
        'confirm' => [
            'label' => 'Potwierdź',
            'alert' => 'Wymaga potwierdzenia',
            'hint' => [
                'capture' => 'Potwierdź, że chcesz przechwycić tę kwotę.',
                'refund' => 'Potwierdź, że chcesz zwrócić tę kwotę.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Notatki',
            'placeholder' => 'Brak notatek',
        ],
        'delivery_instructions' => [
            'label' => 'Informacje do dostawy',
        ],
        'shipping_total' => [
            'label' => 'Koszty dostawy',
        ],
        'paid' => [
            'label' => 'Opłacone',
        ],
        'refund' => [
            'label' => 'Zwrócone',
        ],
        'unit_price' => [
            'label' => 'Cena jednostkowa',
        ],
        'quantity' => [
            'label' => 'Ilość',
        ],
        'sub_total' => [
            'label' => 'Suma produktów',
        ],
        'discount_total' => [
            'label' => 'Suma zniżek',
        ],
        'total' => [
            'label' => 'Suma',
        ],
        'current_stock_level' => [
            'message' => 'Stan magazynowy: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'w momencie zamówienia: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Numer zamówienia',
        ],
        'customer_reference' => [
            'label' => 'Numer klienta',
        ],
        'channel' => [
            'label' => 'Kanał',
        ],
        'date_created' => [
            'label' => 'Data utworzenia',
        ],
        'date_placed' => [
            'label' => 'Data złożenia',
        ],
        'new_returning' => [
            'label' => 'Nowy/Klient powracający',
        ],
        'new_customer' => [
            'label' => 'Nowy klient',
        ],
        'returning_customer' => [
            'label' => 'Klient powracający',
        ],
        'shipping_address' => [
            'label' => 'Adres dostawy',
        ],
        'billing_address' => [
            'label' => 'Adres rozliczeniowy',
        ],
        'address_not_set' => [
            'label' => 'Adres nieustawiony',
        ],
        'billing_matches_shipping' => [
            'label' => 'Adres rozliczeniowy jest taki sam jak adres dostawy',
        ],
        'additional_info' => [
            'label' => 'Dodatkowe informacje',
        ],
        'no_additional_info' => [
            'label' => 'Brak dodatkowych informacji',
        ],
        'tags' => [
            'label' => 'Tagi',
        ],
        'timeline' => [
            'label' => 'Historia',
        ],
        'transactions' => [
            'label' => 'Transakcje',
            'placeholder' => 'Brak transakcji',
        ],
        'alert' => [
            'requires_capture' => 'To zamówienie nadal wymaga przechwycenia płatności.',
            'partially_refunded' => 'To zamówienie zostało częściowo zwrócone.',
            'refunded' => 'To zamówienie zostało zwrócone.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Aktualizuj statusy',
            'notification' => 'Statusy zamówień zostały zaktualizowane',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'Nowy status',
            ],
            'additional_content' => [
                'label' => 'Dodatkowa treść',
            ],
            'additional_email_recipient' => [
                'label' => 'Dodatkowy odbiorca email',
                'placeholder' => 'opcjonalnie',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Pobierz fakturę',
            'notification' => 'Faktura została pobrana',
        ],
        'edit_address' => [
            'label' => 'Edytuj adres',
            'notification' => [
                'error' => 'Błąd',
                'billing_address' => [
                    'saved' => 'Adres rozliczeniowy zapisany',
                ],
                'shipping_address' => [
                    'saved' => 'Adres dostawy zapisany',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Edytuj tagi',
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Przechwyć płatność',
            'notification' => [
                'error' => 'Wystąpił problem z przechwyceniem płatności',
                'success' => 'Płatność przechwycona',
            ],
        ],
        'refund_payment' => [
            'label' => 'Zwróć płatność',
            'notification' => [
                'error' => 'Wystąpił problem ze zwróceniem płatności',
                'success' => 'Płatność zwrócona',
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
