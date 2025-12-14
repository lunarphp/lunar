<?php

return [

    'label' => 'Comandă',

    'plural_label' => 'Comenzi',

    'breadcrumb' => [
        'manage' => 'Gestionează',
    ],

    'tabs' => [
        'all' => 'Toate',
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
            'label' => 'Stare',
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
            'label' => 'Stare',
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

];
