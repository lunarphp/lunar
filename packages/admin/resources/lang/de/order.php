<?php

return [

    'label' => 'Bestellung',

    'plural_label' => 'Bestellungen',

    'breadcrumb' => [
        'manage' => 'Verwalten',
    ],

    'transactions' => [
        'capture' => 'Erfasst',
        'intent' => 'Absicht',
        'refund' => 'Erstattet',
        'failed' => 'Fehlgeschlagen',
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Referenz',
        ],
        'customer_reference' => [
            'label' => 'Kundenreferenz',
        ],
        'customer' => [
            'label' => 'Kunde',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'postcode' => [
            'label' => 'Postleitzahl',
        ],
        'email' => [
            'label' => 'E-Mail',
            'copy_message' => 'E-Mail-Adresse kopiert',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Gesamt',
        ],
        'date' => [
            'label' => 'Datum',
        ],
        'new_customer' => [
            'label' => 'Kundentyp',
        ],
        'placed_after' => [
            'label' => 'Plaziert nach',
        ],
        'placed_before' => [
            'label' => 'Plaziert vor',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Vorname',
            ],
            'last_name' => [
                'label' => 'Nachname',
            ],
            'line_one' => [
                'label' => 'Adresszeile 1',
            ],
            'line_two' => [
                'label' => 'Adresszeile 2',
            ],
            'line_three' => [
                'label' => 'Adresszeile 3',
            ],
            'company_name' => [
                'label' => 'Firmenname',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'E-Mail-Adresse',
            ],
            'city' => [
                'label' => 'Stadt',
            ],
            'state' => [
                'label' => 'Staat / Provinz',
            ],
            'postcode' => [
                'label' => 'Postleitzahl',
            ],
            'country_id' => [
                'label' => 'Land',
            ],
        ],

        'reference' => [
            'label' => 'Referenz',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'transaction' => [
            'label' => 'Transaktion',
        ],
        'amount' => [
            'label' => 'Betrag',

            'hint' => [
                'less_than_total' => 'Sie sind dabei, einen Betrag zu erfassen, der kleiner ist als der gesamte Transaktionswert',
            ],
        ],

        'notes' => [
            'label' => 'Notizen',
        ],
        'confirm' => [
            'label' => 'Bestätigen',

            'alert' => 'Bestätigung erforderlich',

            'hint' => [
                'capture' => 'Bitte bestätigen Sie, dass Sie diese Zahlung erfassen möchten',
                'refund' => 'Bitte bestätigen Sie, dass Sie diesen Betrag erstatten möchten.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Notizen',
            'placeholder' => 'Keine Notizen zu dieser Bestellung',
        ],
        'delivery_instructions' => [
            'label' => 'Lieferanweisungen',
        ],
        'shipping_total' => [
            'label' => 'Versandkosten Gesamt',
        ],
        'paid' => [
            'label' => 'Bezahlt',
        ],
        'refund' => [
            'label' => 'Rückerstattung',
        ],
        'unit_price' => [
            'label' => 'Stückpreis',
        ],
        'quantity' => [
            'label' => 'Menge',
        ],
        'sub_total' => [
            'label' => 'Zwischensumme',
        ],
        'discount_total' => [
            'label' => 'Rabatt Gesamt',
        ],
        'total' => [
            'label' => 'Gesamt',
        ],
        'current_stock_level' => [
            'message' => 'Aktueller Lagerbestand: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'Zum Zeitpunkt der Bestellung: :count',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Referenz',
        ],
        'customer_reference' => [
            'label' => 'Kundenreferenz',
        ],
        'channel' => [
            'label' => 'Kanal',
        ],
        'date_created' => [
            'label' => 'Erstellungsdatum',
        ],
        'date_placed' => [
            'label' => 'Bestelldatum',
        ],
        'new_returning' => [
            'label' => 'Neu / Wiederkehrend',
        ],
        'new_customer' => [
            'label' => 'Neukunde',
        ],
        'returning_customer' => [
            'label' => 'Wiederkehrender Kunde',
        ],
        'shipping_address' => [
            'label' => 'Lieferadresse',
        ],
        'billing_address' => [
            'label' => 'Rechnungsadresse',
        ],
        'address_not_set' => [
            'label' => 'Keine Adresse festgelegt',
        ],
        'billing_matches_shipping' => [
            'label' => 'Gleich wie Lieferadresse',
        ],
        'additional_info' => [
            'label' => 'Zusätzliche Informationen',
        ],
        'no_additional_info' => [
            'label' => 'Keine zusätzlichen Informationen',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'timeline' => [
            'label' => 'Zeitleiste',
        ],
        'transactions' => [
            'label' => 'Transaktionen',
            'placeholder' => 'Keine Transaktionen',
        ],
        'alert' => [
            'requires_capture' => 'Diese Bestellung erfordert noch die Erfassung der Zahlung.',
            'partially_refunded' => 'Diese Bestellung wurde teilweise erstattet.',
            'refunded' => 'Diese Bestellung wurde erstattet.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Status aktualisieren',
            'notification' => 'Bestellstatus aktualisiert',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Neuer Status',
            ],
            'additional_content' => [
                'label' => 'Zusätzlicher Inhalt',
            ],
            'additional_email_recipient' => [
                'label' => 'Zusätzlicher E-Mail-Empfänger',
                'placeholder' => 'optional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'PDF herunterladen',
            'notification' => 'Bestell-PDF wird heruntergeladen',
        ],
        'edit_address' => [
            'label' => 'Bearbeiten',

            'notification' => [
                'error' => 'Fehler',

                'billing_address' => [
                    'saved' => 'Rechnungsadresse gespeichert',
                ],

                'shipping_address' => [
                    'saved' => 'Lieferadresse gespeichert',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Bearbeiten',
        ],
        'capture_payment' => [
            'label' => 'Zahlung erfassen',

            'notification' => [
                'error' => 'Bei der Erfassung gab es ein Problem',
                'success' => 'Erfassung erfolgreich',
            ],
        ],
        'refund_payment' => [
            'label' => 'Rückerstattung',

            'notification' => [
                'error' => 'Bei der Rückerstattung gab es ein Problem',
                'success' => 'Rückerstattung erfolgreich',
            ],
        ],
    ],

];
