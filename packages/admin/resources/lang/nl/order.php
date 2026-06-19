<?php

return [
    'label' => 'Bestelling',
    'plural_label' => 'Bestellingen',
    'breadcrumb' => [
        'manage' => 'Beheren',
    ],
    'transactions' => [
        'capture' => 'Geïncasseerd',
        'intent' => 'Voorgenomen',
        'refund' => 'Terugbetaald',
        'failed' => 'Mislukt',
    ],
    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Referentie',
        ],
        'customer_reference' => [
            'label' => 'Klantreferentie',
        ],
        'customer' => [
            'label' => 'Klant',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'postcode' => [
            'label' => 'Postcode',
        ],
        'email' => [
            'label' => 'E-mail',
            'copy_message' => 'E-mailadres gekopieerd',
        ],
        'phone' => [
            'label' => 'Telefoon',
        ],
        'total' => [
            'label' => 'Totaal',
        ],
        'date' => [
            'label' => 'Datum',
        ],
        'new_customer' => [
            'label' => 'Klanttype',
        ],
        'placed_after' => [
            'label' => 'Geplaatst na',
        ],
        'placed_before' => [
            'label' => 'Geplaatst voor',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Voornaam',
            ],
            'last_name' => [
                'label' => 'Achternaam',
            ],
            'line_one' => [
                'label' => 'Adresregel 1',
            ],
            'line_two' => [
                'label' => 'Adresregel 2',
            ],
            'line_three' => [
                'label' => 'Adresregel 3',
            ],
            'company_name' => [
                'label' => 'Bedrijfsnaam',
            ],
            'tax_identifier' => [
                'label' => 'BTW-nummer',
            ],
            'contact_phone' => [
                'label' => 'Telefoon',
            ],
            'contact_email' => [
                'label' => 'E-mailadres',
            ],
            'city' => [
                'label' => 'Stad',
            ],
            'state' => [
                'label' => 'Staat / Provincie',
            ],
            'postcode' => [
                'label' => 'Postcode',
            ],
            'country_id' => [
                'label' => 'Land',
            ],
        ],
        'reference' => [
            'label' => 'Referentie',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Transactie',
        ],
        'amount' => [
            'label' => 'Bedrag',
            'hint' => [
                'less_than_total' => 'Je staat op het punt een bedrag te incasseren dat minder is dan de totale transactiewaarde',
            ],
        ],
        'notes' => [
            'label' => 'Notities',
        ],
        'confirm' => [
            'label' => 'Bevestigen',
            'alert' => 'Bevestiging vereist',
            'hint' => [
                'capture' => 'Bevestig alstublieft dat u deze betaling wilt incasseren',
                'refund' => 'Bevestig alstublieft dat u dit bedrag wilt terugbetalen.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Notities',
            'placeholder' => 'Geen notities bij deze bestelling',
        ],
        'delivery_instructions' => [
            'label' => 'Leveringsinstructies',
        ],
        'shipping_total' => [
            'label' => 'Verzendkosten Totaal',
        ],
        'paid' => [
            'label' => 'Betaald',
        ],
        'refund' => [
            'label' => 'Terugbetaling',
        ],
        'unit_price' => [
            'label' => 'Eenheidsprijs',
        ],
        'quantity' => [
            'label' => 'Aantal',
        ],
        'sub_total' => [
            'label' => 'Subtotaal',
        ],
        'discount_total' => [
            'label' => 'Korting Totaal',
        ],
        'total' => [
            'label' => 'Totaal',
        ],
        'current_stock_level' => [
            'message' => 'Huidig Voorraadniveau: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'op het moment van bestelling: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Referentie',
        ],
        'customer_reference' => [
            'label' => 'Klantreferentie',
        ],
        'channel' => [
            'label' => 'Kanaal',
        ],
        'date_created' => [
            'label' => 'Aanmaakdatum',
        ],
        'date_placed' => [
            'label' => 'Plaatsingsdatum',
        ],
        'new_returning' => [
            'label' => 'Nieuw / Terugkerend',
        ],
        'new_customer' => [
            'label' => 'Nieuwe Klant',
        ],
        'returning_customer' => [
            'label' => 'Terugkerende Klant',
        ],
        'shipping_address' => [
            'label' => 'Verzendadres',
        ],
        'billing_address' => [
            'label' => 'Factuuradres',
        ],
        'address_not_set' => [
            'label' => 'Geen adres ingesteld',
        ],
        'billing_matches_shipping' => [
            'label' => 'Zelfde als verzendadres',
        ],
        'additional_info' => [
            'label' => 'Aanvullende informatie',
        ],
        'no_additional_info' => [
            'label' => 'Geen aanvullende informatie',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'timeline' => [
            'label' => 'Tijdlijn',
        ],
        'transactions' => [
            'label' => 'Transacties',
            'placeholder' => 'Geen transacties',
        ],
        'alert' => [
            'requires_capture' => 'Deze bestelling moet nog worden geïncasseerd.',
            'partially_refunded' => 'Deze bestelling is gedeeltelijk terugbetaald.',
            'refunded' => 'Deze bestelling is terugbetaald.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Status Bijwerken',
            'notification' => 'Bestellingsstatus bijgewerkt',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'Nieuwe status',
            ],
            'additional_content' => [
                'label' => 'Aanvullende inhoud',
            ],
            'additional_email_recipient' => [
                'label' => 'Aanvullende e-mailontvanger',
                'placeholder' => 'optioneel',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'PDF Downloaden',
            'notification' => 'Bestelling PDF downloaden',
        ],
        'edit_address' => [
            'label' => 'Bewerken',
            'notification' => [
                'error' => 'Fout',
                'billing_address' => [
                    'saved' => 'Factuuradres opgeslagen',
                ],
                'shipping_address' => [
                    'saved' => 'Verzendadres opgeslagen',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Bewerken',
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Betaling Incasseren',
            'notification' => [
                'error' => 'Er was een probleem met het incasseren',
                'success' => 'Incasseren succesvol',
            ],
        ],
        'refund_payment' => [
            'label' => 'Terugbetaling',
            'notification' => [
                'error' => 'Er was een probleem met de terugbetaling',
                'success' => 'Terugbetaling succesvol',
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
