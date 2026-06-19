<?php

return [
    'label' => 'Narudžba',
    'plural_label' => 'Narudžbe',
    'breadcrumb' => [
        'manage' => 'Upravljanje',
    ],
    'transactions' => [
        'capture' => 'Naplaćeno',
        'intent' => 'Namjera',
        'refund' => 'Vraćeno',
        'failed' => 'Neuspješno',
    ],
    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Referenca',
        ],
        'customer_reference' => [
            'label' => 'Referenca kupca',
        ],
        'customer' => [
            'label' => 'Kupac',
        ],
        'tags' => [
            'label' => 'Oznake',
        ],
        'postcode' => [
            'label' => 'Poštanski broj',
        ],
        'email' => [
            'label' => 'E-mail',
            'copy_message' => 'E-mail adresa kopirana',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Ukupno',
        ],
        'date' => [
            'label' => 'Datum',
        ],
        'new_customer' => [
            'label' => 'Tip kupca',
        ],
        'placed_after' => [
            'label' => 'Naručeno nakon',
        ],
        'placed_before' => [
            'label' => 'Naručeno prije',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Ime',
            ],
            'last_name' => [
                'label' => 'Prezime',
            ],
            'line_one' => [
                'label' => 'Adresa redak 1',
            ],
            'line_two' => [
                'label' => 'Adresa redak 2',
            ],
            'line_three' => [
                'label' => 'Adresa redak 3',
            ],
            'company_name' => [
                'label' => 'Naziv tvrtke',
            ],
            'tax_identifier' => [
                'label' => 'Tax Identifier',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'E-mail adresa',
            ],
            'city' => [
                'label' => 'Grad',
            ],
            'state' => [
                'label' => 'Županija',
            ],
            'postcode' => [
                'label' => 'Poštanski broj',
            ],
            'country_id' => [
                'label' => 'Zemlja',
            ],
        ],
        'reference' => [
            'label' => 'Referenca',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Transakcija',
        ],
        'amount' => [
            'label' => 'Iznos',
            'hint' => [
                'less_than_total' => 'Želite naplatiti iznos koji je manji od ukupne vrijednosti transakcije',
            ],
        ],
        'notes' => [
            'label' => 'Bilješke',
        ],
        'confirm' => [
            'label' => 'Potvrdi',
            'alert' => 'Potrebna je potvrda',
            'hint' => [
                'capture' => 'Molimo potvrdite da želite naplatiti ovu uplatu',
                'refund' => 'Molimo potvrdite da želite vratiti ovaj iznos.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Bilješke',
            'placeholder' => 'Nema bilješki za ovu narudžbu',
        ],
        'delivery_instructions' => [
            'label' => 'Upute za dostavu',
        ],
        'shipping_total' => [
            'label' => 'Ukupni troškovi dostave',
        ],
        'paid' => [
            'label' => 'Plaćeno',
        ],
        'refund' => [
            'label' => 'Povrat',
        ],
        'unit_price' => [
            'label' => 'Jedinična cijena',
        ],
        'quantity' => [
            'label' => 'Količina',
        ],
        'sub_total' => [
            'label' => 'Međuzbroj',
        ],
        'discount_total' => [
            'label' => 'Ukupni popust',
        ],
        'total' => [
            'label' => 'Ukupno',
        ],
        'current_stock_level' => [
            'message' => 'Trenutna razina zaliha: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'U trenutku narudžbe: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Referenca',
        ],
        'customer_reference' => [
            'label' => 'Referenca kupca',
        ],
        'channel' => [
            'label' => 'Kanal',
        ],
        'date_created' => [
            'label' => 'Datum stvaranja',
        ],
        'date_placed' => [
            'label' => 'Datum narudžbe',
        ],
        'new_returning' => [
            'label' => 'Novi / Stalni',
        ],
        'new_customer' => [
            'label' => 'Novi kupac',
        ],
        'returning_customer' => [
            'label' => 'Stalni kupac',
        ],
        'shipping_address' => [
            'label' => 'Adresa dostave',
        ],
        'billing_address' => [
            'label' => 'Adresa naplate',
        ],
        'address_not_set' => [
            'label' => 'Adresa nije postavljena',
        ],
        'billing_matches_shipping' => [
            'label' => 'Jednako kao adresa dostave',
        ],
        'additional_info' => [
            'label' => 'Dodatne informacije',
        ],
        'no_additional_info' => [
            'label' => 'Nema dodatnih informacija',
        ],
        'tags' => [
            'label' => 'Oznake',
        ],
        'timeline' => [
            'label' => 'Vremenska crta',
        ],
        'transactions' => [
            'label' => 'Transakcije',
            'placeholder' => 'Nema transakcija',
        ],
        'alert' => [
            'requires_capture' => 'Za ovu narudžbu još uvijek je potrebno evidentirati plaćanje.',
            'partially_refunded' => 'Za ovu narudžbu je djelomično izvršen povrat sredstava',
            'refunded' => 'Ova narudžba je vraćena.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Ažuriraj status',
            'notification' => 'Status narudžbe ažuriran',
        ],
        'update_status' => [
            'label' => 'Ažuriraj status',
            'notification' => 'Status narudžbe ažuriran',
            'new_status' => [
                'label' => 'Novi status',
            ],
            'additional_content' => [
                'label' => 'Dodatni sadržaj',
            ],
            'additional_email_recipient' => [
                'label' => 'Dodatni primatelj e-maila',
                'placeholder' => 'neobavezno',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Preuzmi PDF',
            'notification' => 'PDF narudžbe se preuzima',
        ],
        'edit_address' => [
            'label' => 'Uredi',
            'notification' => [
                'error' => 'Greška',
                'billing_address' => [
                    'saved' => 'Adresa naplate spremljena',
                ],
                'shipping_address' => [
                    'saved' => 'Adresa dostave spremljena',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Uredi',
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Naplati uplatu',
            'notification' => [
                'error' => 'Došlo je do problema prilikom naplate',
                'success' => 'Naplata uspješna',
            ],
        ],
        'refund_payment' => [
            'label' => 'Povrat',
            'notification' => [
                'error' => 'Došlo je do problema prilikom povrata',
                'success' => 'Povrat uspješan',
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
