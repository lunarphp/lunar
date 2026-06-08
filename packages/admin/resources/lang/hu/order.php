<?php

return [
    'label' => 'Rendelés',
    'plural_label' => 'Rendelések',
    'breadcrumb' => [
        'manage' => 'Kezelés',
    ],
    'tabs' => [
        'all' => 'Mind',
    ],
    'transactions' => [
        'capture' => 'Lekönyvelve',
        'intent' => 'Fizetési szándék',
        'refund' => 'Visszatérítve',
        'failed' => 'Sikertelen',
    ],
    'table' => [
        'status' => [
            'label' => 'Státusz',
        ],
        'reference' => [
            'label' => 'Hivatkozás',
        ],
        'customer_reference' => [
            'label' => 'Vásárlói azonosító',
        ],
        'customer' => [
            'label' => 'Vásárló',
        ],
        'tags' => [
            'label' => 'Címkék',
        ],
        'postcode' => [
            'label' => 'Irányítószám',
        ],
        'email' => [
            'label' => 'E-mail',
            'copy_message' => 'E-mail cím másolva',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Végösszeg',
        ],
        'date' => [
            'label' => 'Dátum',
        ],
        'new_customer' => [
            'label' => 'Vásárló típusa',
        ],
        'placed_after' => [
            'label' => 'Rendelés ideje után',
        ],
        'placed_before' => [
            'label' => 'Rendelés ideje előtt',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Keresztnév',
            ],
            'last_name' => [
                'label' => 'Vezetéknév',
            ],
            'line_one' => [
                'label' => 'Utca, házszám',
            ],
            'line_two' => [
                'label' => 'Emelet, ajtó',
            ],
            'line_three' => [
                'label' => 'Egyéb címadat',
            ],
            'company_name' => [
                'label' => 'Cégnév',
            ],
            'tax_identifier' => [
                'label' => 'Adóazonosító',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'E-mail cím',
            ],
            'city' => [
                'label' => 'Város',
            ],
            'state' => [
                'label' => 'Megye / Tartomány',
            ],
            'postcode' => [
                'label' => 'Irányítószám',
            ],
            'country_id' => [
                'label' => 'Ország',
            ],
        ],
        'reference' => [
            'label' => 'Hivatkozás',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Tranzakció',
        ],
        'amount' => [
            'label' => 'Összeg',
            'hint' => [
                'less_than_total' => 'Ön kevesebb összeget készül lekönyvelni, mint a teljes tranzakció értéke',
            ],
        ],
        'notes' => [
            'label' => 'Megjegyzések',
        ],
        'confirm' => [
            'label' => 'Megerősítés',
            'alert' => 'Megerősítés szükséges',
            'hint' => [
                'capture' => 'Kérjük, erősítse meg, hogy le akarja könyvelni ezt a fizetést',
                'refund' => 'Kérjük, erősítse meg, hogy vissza kívánja téríteni ezt az összeget.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Megjegyzések',
            'placeholder' => 'Nincsenek megjegyzések ehhez a rendeléshez',
        ],
        'delivery_instructions' => [
            'label' => 'Szállítási utasítások',
        ],
        'shipping_total' => [
            'label' => 'Szállítási díj',
        ],
        'paid' => [
            'label' => 'Kifizetve',
        ],
        'refund' => [
            'label' => 'Visszatérítés',
        ],
        'unit_price' => [
            'label' => 'Egységár',
        ],
        'quantity' => [
            'label' => 'Mennyiség',
        ],
        'sub_total' => [
            'label' => 'Részösszeg',
        ],
        'discount_total' => [
            'label' => 'Kedvezmény összege',
        ],
        'total' => [
            'label' => 'Végösszeg',
        ],
        'current_stock_level' => [
            'message' => 'Jelenlegi készletszint: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'rendelés idején: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Hivatkozás',
        ],
        'customer_reference' => [
            'label' => 'Vásárlói azonosító',
        ],
        'channel' => [
            'label' => 'Csatorna',
        ],
        'date_created' => [
            'label' => 'Létrehozva',
        ],
        'date_placed' => [
            'label' => 'Rendelés dátuma',
        ],
        'new_returning' => [
            'label' => 'Új / Visszatérő',
        ],
        'new_customer' => [
            'label' => 'Új vásárló',
        ],
        'returning_customer' => [
            'label' => 'Visszatérő vásárló',
        ],
        'shipping_address' => [
            'label' => 'Szállítási cím',
        ],
        'billing_address' => [
            'label' => 'Számlázási cím',
        ],
        'address_not_set' => [
            'label' => 'Nincs cím megadva',
        ],
        'billing_matches_shipping' => [
            'label' => 'Ugyanaz, mint a szállítási cím',
        ],
        'additional_info' => [
            'label' => 'További információ',
        ],
        'no_additional_info' => [
            'label' => 'Nincs további információ',
        ],
        'tags' => [
            'label' => 'Címkék',
        ],
        'timeline' => [
            'label' => 'Idővonal',
        ],
        'transactions' => [
            'label' => 'Tranzakciók',
            'placeholder' => 'Nincsenek tranzakciók',
        ],
        'alert' => [
            'requires_capture' => 'Ennél a rendelésnél még fizetést kell lekönyvelni.',
            'partially_refunded' => 'Ennél a rendelésnél részleges visszatérítés történt.',
            'refunded' => 'Ennél a rendelésnél visszatérítés történt.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Státusz frissítése',
            'notification' => 'Rendelések státusza frissítve',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'Új státusz',
            ],
            'additional_content' => [
                'label' => 'További tartalom',
            ],
            'additional_email_recipient' => [
                'label' => 'További e-mail címzett',
                'placeholder' => 'opcionális',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'PDF letöltése',
            'notification' => 'Rendelés PDF letöltése',
        ],
        'edit_address' => [
            'label' => 'Szerkesztés',
            'notification' => [
                'error' => 'Hiba',
                'billing_address' => [
                    'saved' => 'Számlázási cím mentve',
                ],
                'shipping_address' => [
                    'saved' => 'Szállítási cím mentve',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Szerkesztés',
            'form' => [
                'tags' => [
                    'label' => 'Címkék',
                    'helper_text' => 'Címkék elválasztása Enterrel, Tab-bal vagy vesszővel (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Fizetés lekönyvelése',
            'notification' => [
                'error' => 'Hiba történt a lekönyvelés során',
                'success' => 'Lekönyvelés sikeres',
            ],
        ],
        'refund_payment' => [
            'label' => 'Visszatérítés',
            'notification' => [
                'error' => 'Hiba történt a visszatérítés során',
                'success' => 'Visszatérítés sikeres',
            ],
        ],
    ],

    'fulfilments' => [
        'heading' => 'Fulfilments',
        'empty' => 'No fulfilments yet.',
        'columns' => [
            'reference' => 'Reference',
            'state' => 'State',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Shipped at',
        ],
        'actions' => [
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
            'cancel' => [
                'label' => 'Cancel',
                'notification' => [
                    'success' => 'Fulfilment cancelled.',
                    'error' => 'Could not cancel fulfilment.',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'notification' => [
                    'success' => 'Fulfilment returned.',
                    'error' => 'Could not return fulfilment.',
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
                'label' => 'Merge selected',
                'modal_heading' => 'Merge fulfilments',
                'notification' => [
                    'success' => 'Fulfilments merged.',
                    'error' => 'Could not merge fulfilments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'sub_total' => 'Sub Total',
            'discount_total' => 'Discount Total',
            'total' => 'Total',
            'stock_level' => 'Current Stock Level: :count',
            'of' => 'of :count',
            'outstanding' => 'Outstanding: :count',
            'tracking_number' => 'Tracking number',
            'tracking_url' => 'Tracking URL',
            'shipping_method' => 'Shipping method',
            'move_quantity' => 'Quantity to move out',
        ],
    ],

    'other_items' => [
        'heading' => 'Other items',
    ],
];
