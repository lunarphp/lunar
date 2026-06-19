<?php

return [
    'label' => 'Rendelés',
    'plural_label' => 'Rendelések',
    'breadcrumb' => [
        'manage' => 'Kezelés',
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
        'heading' => 'Teljesítések',
        'unreferenced' => ':id. számú teljesítés',
        'on_hold' => 'Felfüggesztve',
        'empty' => 'Még nincs teljesítés.',
        'columns' => [
            'reference' => 'Hivatkozás',
            'state' => 'Állapot',
            'items' => 'Tételek',
            'tracking' => 'Nyomon követés',
            'shipped_at' => 'Kiszállítva',
            'handed_over' => [
                'shipping' => 'Kiszállítva',
                'collection' => 'Átvéve',
                'digital' => 'Hozzáférhetővé téve',
            ],
            'handed_over_default' => 'Teljesítve',
        ],
        'actions' => [
            'more' => 'További műveletek',
            'notify' => 'Vásárló értesítése',
            'add_tracking' => [
                'label' => 'Nyomon követés hozzáadása',
                'modal_heading' => 'Nyomon követés hozzáadása',
                'notification' => [
                    'success' => 'Nyomon követés hozzáadva.',
                    'error' => 'A nyomon követést nem sikerült hozzáadni.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Nyomon követés eltávolítása',
                'notification' => [
                    'success' => 'Nyomon követés eltávolítva.',
                    'error' => 'A nyomon követést nem sikerült eltávolítani.',
                ],
            ],
            'create' => [
                'label' => 'Teljesítés létrehozása',
                'modal_heading' => 'Teljesítés létrehozása',
                'empty' => 'Minden tétel már teljesítve van.',
                'notification' => [
                    'success' => 'Teljesítés létrehozva.',
                    'error' => 'A teljesítést nem sikerült létrehozni.',
                ],
            ],
            'ship' => [
                'label' => 'Kiszállítottnak jelölés',
                'modal_heading' => 'Teljesítés megjelölése kiszállítottként',
                'notification' => [
                    'success' => 'Teljesítés kiszállítottként megjelölve.',
                    'error' => 'A teljesítést nem sikerült kiszállítani.',
                ],
            ],
            'fulfil' => [
                'label' => 'Teljesítettnek jelölés',
                'modal_heading' => 'Teljesítés megjelölése teljesítettként',
                'labels' => [
                    'collection' => 'Átvettnek jelölés',
                ],
                'notification' => [
                    'success' => 'Teljesítés teljesítettként megjelölve.',
                    'error' => 'A teljesítést nem sikerült teljesíteni.',
                ],
            ],
            'cancel' => [
                'label' => 'Teljesítés visszavonása',
                'modal_heading' => 'Teljesítés visszavonása',
                'description' => 'Ez visszaállítja a teljesítést függő állapotba, hogy újra folytatható legyen. A kiszállítási adatok törlődnek.',
                'notification' => [
                    'success' => 'Teljesítés visszavonva.',
                    'error' => 'A teljesítést nem sikerült visszavonni.',
                ],
            ],
            'change_location' => [
                'label' => 'Telephely módosítása',
                'modal_heading' => 'Teljesítés telephelyének módosítása',
                'field' => 'Telephely',
                'notification' => [
                    'success' => 'Teljesítés telephelye frissítve.',
                    'error' => 'A teljesítés telephelyét nem sikerült módosítani.',
                ],
            ],
            'return' => [
                'label' => 'Visszaküldés',
                'notification' => [
                    'success' => 'Teljesítés visszaküldve.',
                    'error' => 'A teljesítést nem sikerült visszaküldeni.',
                ],
            ],
            'update_status' => [
                'label' => 'Állapot frissítése',
            ],
            'transition' => [
                'modal_heading' => 'Teljesítés megjelölése mint :status?',
                'notification' => [
                    'success' => 'Teljesítés állapota frissítve.',
                    'error' => 'A teljesítés állapotát nem sikerült frissíteni.',
                ],
            ],
            'undo_return' => [
                'label' => 'Visszaküldés visszavonása',
                'notification' => [
                    'success' => 'Visszaküldés visszavonva.',
                    'error' => 'A visszaküldést nem sikerült visszavonni.',
                ],
            ],
            'hold' => [
                'label' => 'Teljesítés felfüggesztése',
                'modal_heading' => 'Teljesítés felfüggesztése',
                'reason' => 'Indok',
                'note' => 'Megjegyzés',
                'notification' => [
                    'success' => 'Teljesítés felfüggesztve.',
                    'error' => 'A teljesítést nem sikerült felfüggeszteni.',
                ],
            ],
            'release' => [
                'label' => 'Felfüggesztés feloldása',
                'notification' => [
                    'success' => 'Teljesítés felfüggesztése feloldva.',
                    'error' => 'A teljesítés felfüggesztését nem sikerült feloldani.',
                ],
            ],
            'split' => [
                'label' => 'Szétbontás',
                'confirm' => 'Teljesítés szétbontása',
                'cancel' => 'Mégse',
                'empty' => 'Válassz ki egy szétbontandó mennyiséget.',
                'modal_heading' => 'Teljesítés szétbontása',
                'notification' => [
                    'success' => 'Teljesítés szétbontva.',
                    'error' => 'A teljesítést nem sikerült szétbontani.',
                ],
            ],
            'merge' => [
                'label' => 'Összevonás',
                'confirm' => 'Teljesítés összevonása',
                'cancel' => 'Mégse',
                'modal_heading' => 'Teljesítés összevonása',
                'description' => 'Válaszd ki az összevonni kívánt tételeket.',
                'target' => 'Összevonás ezzel',
                'empty' => 'Válassz ki tételeket és egy célt az összevonáshoz.',
                'notification' => [
                    'success' => 'Teljesítések összevonva.',
                    'error' => 'A teljesítéseket nem sikerült összevonni.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Mennyiség',
            'tracking' => 'Nyomon követés',
            'tracking_item' => ':number. számú nyomon követés',
            'unit_price' => 'Egységár',
            'sub_total' => 'Részösszeg',
            'discount_total' => 'Kedvezmény összesen',
            'total' => 'Összesen',
            'stock_level' => 'Jelenlegi készletszint: :count',
            'of' => ':count közül',
            'outstanding' => 'Hátralévő: :count',
            'tracking_number' => 'Nyomon követési szám',
            'tracking_url' => 'Nyomon követési URL',
            'carrier' => 'Futárszolgálat',
            'carrier_custom' => 'Egyéni / egyéb',
            'tracking_url_help' => 'Csak olyan futárszolgálatoknál szükséges, amelyeknek nincs automatikus nyomon követési hivatkozása.',
            'shipping_method' => 'Szállítási mód',
            'move_quantity' => 'Áthelyezendő mennyiség',
        ],
    ],

    'other_items' => [
        'heading' => 'Egyéb tételek',
    ],
];
