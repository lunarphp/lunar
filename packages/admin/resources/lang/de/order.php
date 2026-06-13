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
