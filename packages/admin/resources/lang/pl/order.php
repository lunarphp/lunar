<?php

return [

    'label' => 'Zamówienie',

    'plural_label' => 'Zamówienia',

    'breadcrumb' => [
        'manage' => 'Zarządzanie zamówieniami',
    ],

    'tabs' => [
        'all' => 'Wszelkie',
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
            'label' => 'Status',
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
            'label' => 'Status',
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

];
