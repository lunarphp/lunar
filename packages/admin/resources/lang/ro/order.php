<?php

return [
    'label' => 'Comandă',
    'plural_label' => 'Comenzi',
    'breadcrumb' => [
        'manage' => 'Gestionează',
    ],
    'transactions' => [
        'capture' => 'Capturat',
        'intent' => 'Intenție',
        'refund' => 'Rambursat',
        'failed' => 'Eșuat',
    ],
    'table' => [
        'status' => [
            'label' => 'Stare',
        ],
        'reference' => [
            'label' => 'Referință',
        ],
        'customer_reference' => [
            'label' => 'Referință client',
        ],
        'customer' => [
            'label' => 'Client',
        ],
        'tags' => [
            'label' => 'Etichete',
        ],
        'postcode' => [
            'label' => 'Cod poștal',
        ],
        'email' => [
            'label' => 'E-mail',
            'copy_message' => 'Adresă e-mail copiată',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Data',
        ],
        'new_customer' => [
            'label' => 'Tip client',
        ],
        'placed_after' => [
            'label' => 'Plasată după',
        ],
        'placed_before' => [
            'label' => 'Plasată înainte de',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Prenume',
            ],
            'last_name' => [
                'label' => 'Nume',
            ],
            'line_one' => [
                'label' => 'Adresă linia 1',
            ],
            'line_two' => [
                'label' => 'Adresă linia 2',
            ],
            'line_three' => [
                'label' => 'Adresă linia 3',
            ],
            'company_name' => [
                'label' => 'Nume companie',
            ],
            'tax_identifier' => [
                'label' => 'Cod fiscal',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'Adresă e-mail',
            ],
            'city' => [
                'label' => 'Oraș',
            ],
            'state' => [
                'label' => 'Județ / Provincie',
            ],
            'postcode' => [
                'label' => 'Cod poștal',
            ],
            'country_id' => [
                'label' => 'Țară',
            ],
        ],
        'reference' => [
            'label' => 'Referință',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Tranzacție',
        ],
        'amount' => [
            'label' => 'Sumă',
            'hint' => [
                'less_than_total' => 'Urmează să capturezi o sumă mai mică decât valoarea totală a tranzacției',
            ],
        ],
        'notes' => [
            'label' => 'Note',
        ],
        'confirm' => [
            'label' => 'Confirmă',
            'alert' => 'Este necesară confirmarea',
            'hint' => [
                'capture' => 'Confirmă că dorești să capturezi această plată',
                'refund' => 'Confirmă că dorești să rambursezi această sumă.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Note',
            'placeholder' => 'Nu există note pentru această comandă',
        ],
        'delivery_instructions' => [
            'label' => 'Instrucțiuni de livrare',
        ],
        'shipping_total' => [
            'label' => 'Total livrare',
        ],
        'paid' => [
            'label' => 'Plătit',
        ],
        'refund' => [
            'label' => 'Rambursare',
        ],
        'unit_price' => [
            'label' => 'Preț unitar',
        ],
        'quantity' => [
            'label' => 'Cantitate',
        ],
        'sub_total' => [
            'label' => 'Subtotal',
        ],
        'discount_total' => [
            'label' => 'Total reducere',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Stoc curent: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'la momentul comenzii: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Referință',
        ],
        'customer_reference' => [
            'label' => 'Referință client',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Data creării',
        ],
        'date_placed' => [
            'label' => 'Data plasării',
        ],
        'new_returning' => [
            'label' => 'Nou / Revenit',
        ],
        'new_customer' => [
            'label' => 'Client nou',
        ],
        'returning_customer' => [
            'label' => 'Client revenit',
        ],
        'shipping_address' => [
            'label' => 'Adresă de livrare',
        ],
        'billing_address' => [
            'label' => 'Adresă de facturare',
        ],
        'address_not_set' => [
            'label' => 'Nicio adresă setată',
        ],
        'billing_matches_shipping' => [
            'label' => 'La fel ca adresa de livrare',
        ],
        'additional_info' => [
            'label' => 'Informații suplimentare',
        ],
        'no_additional_info' => [
            'label' => 'Nu există informații suplimentare',
        ],
        'tags' => [
            'label' => 'Etichete',
        ],
        'timeline' => [
            'label' => 'Cronologie',
        ],
        'transactions' => [
            'label' => 'Tranzacții',
            'placeholder' => 'Nicio tranzacție',
        ],
        'alert' => [
            'requires_capture' => 'Această comandă necesită în continuare capturarea plății.',
            'partially_refunded' => 'Această comandă a fost rambursată parțial.',
            'refunded' => 'Această comandă a fost rambursată.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Actualizează starea',
            'notification' => 'Starea comenzilor a fost actualizată',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'Stare nouă',
            ],
            'additional_content' => [
                'label' => 'Conținut suplimentar',
            ],
            'additional_email_recipient' => [
                'label' => 'Destinatar e-mail suplimentar',
                'placeholder' => 'opțional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Descarcă PDF',
            'notification' => 'Descărcarea PDF-ului comenzii',
        ],
        'edit_address' => [
            'label' => 'Editează',
            'notification' => [
                'error' => 'Eroare',
                'billing_address' => [
                    'saved' => 'Adresa de facturare a fost salvată',
                ],
                'shipping_address' => [
                    'saved' => 'Adresa de livrare a fost salvată',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Editează',
            'form' => [
                'tags' => [
                    'label' => 'Etichete',
                    'helper_text' => 'Separați etichetele apăsând Enter, Tab sau virgulă (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Capturează plata',
            'notification' => [
                'error' => 'A apărut o problemă la capturare',
                'success' => 'Capturare reușită',
            ],
        ],
        'refund_payment' => [
            'label' => 'Rambursare',
            'notification' => [
                'error' => 'A apărut o problemă la rambursare',
                'success' => 'Rambursare reușită',
            ],
        ],
    ],

    'fulfilments' => [
        'heading' => 'Onorări',
        'unreferenced' => 'Onorarea #:id',
        'on_hold' => 'În așteptare',
        'empty' => 'Nicio onorare încă.',
        'columns' => [
            'reference' => 'Referință',
            'state' => 'Stare',
            'items' => 'Articole',
            'tracking' => 'Urmărire',
            'shipped_at' => 'Expediat la',
            'handed_over' => [
                'shipping' => 'Expediat la',
                'collection' => 'Ridicat la',
                'digital' => 'Furnizat la',
            ],
            'handed_over_default' => 'Onorat la',
        ],
        'actions' => [
            'more' => 'Mai multe acțiuni',
            'notify' => 'Notifică clientul',
            'add_tracking' => [
                'label' => 'Adaugă urmărire',
                'modal_heading' => 'Adaugă urmărire',
                'notification' => [
                    'success' => 'Urmărire adăugată.',
                    'error' => 'Nu s-a putut adăuga urmărirea.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Elimină urmărirea',
                'notification' => [
                    'success' => 'Urmărire eliminată.',
                    'error' => 'Nu s-a putut elimina urmărirea.',
                ],
            ],
            'create' => [
                'label' => 'Creează onorare',
                'modal_heading' => 'Creează onorare',
                'empty' => 'Fiecare linie este deja onorată.',
                'notification' => [
                    'success' => 'Onorare creată.',
                    'error' => 'Nu s-a putut crea onorarea.',
                ],
            ],
            'ship' => [
                'label' => 'Marchează expediat',
                'modal_heading' => 'Marchează onorarea ca expediată',
                'notification' => [
                    'success' => 'Onorare marcată ca expediată.',
                    'error' => 'Nu s-a putut expedia onorarea.',
                ],
            ],
            'fulfil' => [
                'label' => 'Marchează onorat',
                'modal_heading' => 'Marchează onorarea ca onorată',
                'labels' => [
                    'collection' => 'Marchează ridicat',
                ],
                'notification' => [
                    'success' => 'Onorare marcată ca onorată.',
                    'error' => 'Nu s-a putut onora onorarea.',
                ],
            ],
            'cancel' => [
                'label' => 'Anulează onorarea',
                'modal_heading' => 'Anulează onorarea',
                'description' => 'Aceasta readuce onorarea în starea în așteptare pentru a putea fi reluată. Toate detaliile de expediere sunt șterse.',
                'notification' => [
                    'success' => 'Onorare anulată.',
                    'error' => 'Nu s-a putut anula onorarea.',
                ],
            ],
            'change_location' => [
                'label' => 'Schimbă locația',
                'modal_heading' => 'Schimbă locația onorării',
                'field' => 'Locație',
                'notification' => [
                    'success' => 'Locația onorării a fost actualizată.',
                    'error' => 'Nu s-a putut schimba locația onorării.',
                ],
            ],
            'return' => [
                'label' => 'Returnează',
                'notification' => [
                    'success' => 'Onorare returnată.',
                    'error' => 'Nu s-a putut returna onorarea.',
                ],
            ],
            'update_status' => [
                'label' => 'Actualizează starea',
            ],
            'transition' => [
                'modal_heading' => 'Marchezi onorarea ca :status?',
                'notification' => [
                    'success' => 'Starea onorării a fost actualizată.',
                    'error' => 'Nu s-a putut actualiza starea onorării.',
                ],
            ],
            'undo_return' => [
                'label' => 'Anulează returul',
                'notification' => [
                    'success' => 'Retur anulat.',
                    'error' => 'Nu s-a putut anula returul.',
                ],
            ],
            'hold' => [
                'label' => 'Pune în așteptare',
                'modal_heading' => 'Pune onorarea în așteptare',
                'reason' => 'Motiv',
                'note' => 'Notă',
                'notification' => [
                    'success' => 'Onorare pusă în așteptare.',
                    'error' => 'Nu s-a putut pune onorarea în așteptare.',
                ],
            ],
            'release' => [
                'label' => 'Eliberează din așteptare',
                'notification' => [
                    'success' => 'Onorare eliberată.',
                    'error' => 'Nu s-a putut elibera onorarea.',
                ],
            ],
            'split' => [
                'label' => 'Împarte',
                'confirm' => 'Împarte onorarea',
                'cancel' => 'Anulează',
                'empty' => 'Selectează o cantitate de separat.',
                'modal_heading' => 'Împarte onorarea',
                'notification' => [
                    'success' => 'Onorare împărțită.',
                    'error' => 'Nu s-a putut împărți onorarea.',
                ],
            ],
            'merge' => [
                'label' => 'Îmbină',
                'confirm' => 'Îmbină onorarea',
                'cancel' => 'Anulează',
                'modal_heading' => 'Îmbină onorarea',
                'description' => 'Selectează articolele pe care dorești să le îmbini.',
                'target' => 'Îmbină cu',
                'empty' => 'Selectează articole și o destinație pentru îmbinare.',
                'notification' => [
                    'success' => 'Onorări îmbinate.',
                    'error' => 'Nu s-au putut îmbina onorările.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Cantitate',
            'tracking' => 'Urmărire',
            'tracking_item' => 'Urmărire #:number',
            'unit_price' => 'Preț unitar',
            'sub_total' => 'Subtotal',
            'discount_total' => 'Total reduceri',
            'total' => 'Total',
            'stock_level' => 'Nivel curent de stoc: :count',
            'of' => 'din :count',
            'outstanding' => 'Restant: :count',
            'tracking_number' => 'Număr de urmărire',
            'tracking_url' => 'URL de urmărire',
            'carrier' => 'Curier',
            'carrier_custom' => 'Personalizat / altul',
            'tracking_url_help' => 'Necesar doar pentru curierii fără un link de urmărire automat.',
            'shipping_method' => 'Metodă de livrare',
            'move_quantity' => 'Cantitate de mutat',
        ],
    ],

    'other_items' => [
        'heading' => 'Alte articole',
    ],
];
