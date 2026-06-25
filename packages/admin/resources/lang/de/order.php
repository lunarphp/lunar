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
            'tax_identifier' => [
                'label' => 'Tax Identifier',
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
            'label' => 'Order',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
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

    'fulfilments' => [
        'heading' => 'Fulfillments',
        'unreferenced' => 'Fulfillment #:id',
        'on_hold' => 'Angehalten',
        'empty' => 'Noch keine Fulfillments.',
        'columns' => [
            'reference' => 'Referenz',
            'state' => 'Status',
            'items' => 'Artikel',
            'tracking' => 'Sendungsverfolgung',
            'shipped_at' => 'Versendet am',
            'handed_over' => [
                'shipping' => 'Versendet am',
                'collection' => 'Abgeholt am',
                'digital' => 'Bereitgestellt am',
            ],
            'handed_over_default' => 'Erfüllt am',
        ],
        'actions' => [
            'more' => 'Weitere Aktionen',
            'notify' => 'Kunden benachrichtigen',
            'add_tracking' => [
                'label' => 'Sendungsverfolgung hinzufügen',
                'modal_heading' => 'Sendungsverfolgung hinzufügen',
                'notification' => [
                    'success' => 'Sendungsverfolgung hinzugefügt.',
                    'error' => 'Sendungsverfolgung konnte nicht hinzugefügt werden.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Sendungsverfolgung entfernen',
                'notification' => [
                    'success' => 'Sendungsverfolgung entfernt.',
                    'error' => 'Sendungsverfolgung konnte nicht entfernt werden.',
                ],
            ],
            'create' => [
                'label' => 'Fulfillment erstellen',
                'modal_heading' => 'Fulfillment erstellen',
                'empty' => 'Jede Position ist bereits erfüllt.',
                'notification' => [
                    'success' => 'Fulfillment erstellt.',
                    'error' => 'Fulfillment konnte nicht erstellt werden.',
                ],
            ],
            'ship' => [
                'label' => 'Als versendet markieren',
                'modal_heading' => 'Fulfillment als versendet markieren',
                'notification' => [
                    'success' => 'Fulfillment als versendet markiert.',
                    'error' => 'Fulfillment konnte nicht versendet werden.',
                ],
            ],
            'fulfil' => [
                'label' => 'Als erfüllt markieren',
                'modal_heading' => 'Fulfillment als erfüllt markieren',
                'labels' => [
                    'collection' => 'Als abgeholt markieren',
                ],
                'notification' => [
                    'success' => 'Fulfillment als erfüllt markiert.',
                    'error' => 'Fulfillment konnte nicht erfüllt werden.',
                ],
            ],
            'cancel' => [
                'label' => 'Fulfillment stornieren',
                'modal_heading' => 'Fulfillment stornieren',
                'description' => 'Dadurch wird das Fulfillment wieder auf ausstehend gesetzt, sodass es erneut bearbeitet werden kann. Alle Versanddaten werden gelöscht.',
                'notification' => [
                    'success' => 'Fulfillment storniert.',
                    'error' => 'Fulfillment konnte nicht storniert werden.',
                ],
            ],
            'change_location' => [
                'label' => 'Standort ändern',
                'modal_heading' => 'Fulfillment-Standort ändern',
                'field' => 'Standort',
                'notification' => [
                    'success' => 'Fulfillment-Standort aktualisiert.',
                    'error' => 'Fulfillment-Standort konnte nicht geändert werden.',
                ],
            ],
            'return' => [
                'label' => 'Retoure',
                'notification' => [
                    'success' => 'Fulfillment retourniert.',
                    'error' => 'Fulfillment konnte nicht retourniert werden.',
                ],
            ],
            'update_status' => [
                'label' => 'Status aktualisieren',
            ],
            'transition' => [
                'modal_heading' => 'Fulfillment als :status markieren?',
                'notification' => [
                    'success' => 'Fulfillment-Status aktualisiert.',
                    'error' => 'Fulfillment-Status konnte nicht aktualisiert werden.',
                ],
            ],
            'undo_return' => [
                'label' => 'Retoure rückgängig machen',
                'notification' => [
                    'success' => 'Retoure rückgängig gemacht.',
                    'error' => 'Retoure konnte nicht rückgängig gemacht werden.',
                ],
            ],
            'hold' => [
                'label' => 'Fulfillment anhalten',
                'modal_heading' => 'Fulfillment anhalten',
                'reason' => 'Grund',
                'note' => 'Notiz',
                'notification' => [
                    'success' => 'Fulfillment angehalten.',
                    'error' => 'Fulfillment konnte nicht angehalten werden.',
                ],
            ],
            'release' => [
                'label' => 'Anhalten aufheben',
                'notification' => [
                    'success' => 'Fulfillment freigegeben.',
                    'error' => 'Fulfillment konnte nicht freigegeben werden.',
                ],
            ],
            'split' => [
                'label' => 'Aufteilen',
                'confirm' => 'Fulfillment aufteilen',
                'cancel' => 'Abbrechen',
                'empty' => 'Wählen Sie eine Menge zum Aufteilen aus.',
                'modal_heading' => 'Fulfillment aufteilen',
                'notification' => [
                    'success' => 'Fulfillment aufgeteilt.',
                    'error' => 'Fulfillment konnte nicht aufgeteilt werden.',
                ],
            ],
            'merge' => [
                'label' => 'Zusammenführen',
                'confirm' => 'Fulfillment zusammenführen',
                'cancel' => 'Abbrechen',
                'modal_heading' => 'Fulfillment zusammenführen',
                'description' => 'Wählen Sie die Artikel aus, die Sie zusammenführen möchten.',
                'target' => 'Zusammenführen mit',
                'empty' => 'Wählen Sie Artikel und ein Ziel zum Zusammenführen aus.',
                'notification' => [
                    'success' => 'Fulfillments zusammengeführt.',
                    'error' => 'Fulfillments konnten nicht zusammengeführt werden.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Menge',
            'tracking' => 'Sendungsverfolgung',
            'tracking_item' => 'Sendungsnummer #:number',
            'unit_price' => 'Einzelpreis',
            'sub_total' => 'Zwischensumme',
            'discount_total' => 'Rabattsumme',
            'total' => 'Gesamt',
            'stock_level' => 'Aktueller Lagerbestand: :count',
            'of' => 'von :count',
            'outstanding' => 'Ausstehend: :count',
            'tracking_number' => 'Sendungsnummer',
            'tracking_url' => 'Sendungsverfolgungs-URL',
            'carrier' => 'Versanddienstleister',
            'carrier_custom' => 'Benutzerdefiniert / Sonstige',
            'tracking_url_help' => 'Nur erforderlich für Versanddienstleister ohne automatischen Sendungsverfolgungslink.',
            'shipping_method' => 'Versandart',
            'move_quantity' => 'Auszulagernde Menge',
        ],
    ],

    'other_items' => [
        'heading' => 'Weitere Artikel',
    ],
];
