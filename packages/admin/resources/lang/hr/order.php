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
        'heading' => 'Ispunjenja',
        'unreferenced' => 'Ispunjenje #:id',
        'on_hold' => 'Na čekanju',
        'empty' => 'Još nema ispunjenja.',
        'columns' => [
            'reference' => 'Referenca',
            'state' => 'Stanje',
            'items' => 'Stavke',
            'tracking' => 'Praćenje',
            'shipped_at' => 'Otpremljeno',
            'handed_over' => [
                'shipping' => 'Otpremljeno',
                'collection' => 'Preuzeto',
                'digital' => 'Stavljeno na raspolaganje',
            ],
            'handed_over_default' => 'Ispunjeno',
        ],
        'actions' => [
            'more' => 'Više radnji',
            'notify' => 'Obavijesti kupca',
            'add_tracking' => [
                'label' => 'Dodaj praćenje',
                'modal_heading' => 'Dodaj praćenje',
                'notification' => [
                    'success' => 'Praćenje dodano.',
                    'error' => 'Nije moguće dodati praćenje.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Ukloni praćenje',
                'notification' => [
                    'success' => 'Praćenje uklonjeno.',
                    'error' => 'Nije moguće ukloniti praćenje.',
                ],
            ],
            'create' => [
                'label' => 'Stvori ispunjenje',
                'modal_heading' => 'Stvori ispunjenje',
                'empty' => 'Sve stavke već su ispunjene.',
                'notification' => [
                    'success' => 'Ispunjenje stvoreno.',
                    'error' => 'Nije moguće stvoriti ispunjenje.',
                ],
            ],
            'ship' => [
                'label' => 'Označi otpremljeno',
                'modal_heading' => 'Označi ispunjenje kao otpremljeno',
                'notification' => [
                    'success' => 'Ispunjenje označeno kao otpremljeno.',
                    'error' => 'Nije moguće otpremiti ispunjenje.',
                ],
            ],
            'fulfil' => [
                'label' => 'Označi ispunjeno',
                'modal_heading' => 'Označi ispunjenje kao ispunjeno',
                'labels' => [
                    'collection' => 'Označi preuzeto',
                ],
                'notification' => [
                    'success' => 'Ispunjenje označeno kao ispunjeno.',
                    'error' => 'Nije moguće ispuniti ispunjenje.',
                ],
            ],
            'cancel' => [
                'label' => 'Otkaži ispunjenje',
                'modal_heading' => 'Otkaži ispunjenje',
                'description' => 'Ovo vraća ispunjenje na čekanje kako bi se moglo ponovno nastaviti. Svi podaci o otpremi se brišu.',
                'notification' => [
                    'success' => 'Ispunjenje otkazano.',
                    'error' => 'Nije moguće otkazati ispunjenje.',
                ],
            ],
            'change_location' => [
                'label' => 'Promijeni lokaciju',
                'modal_heading' => 'Promijeni lokaciju ispunjenja',
                'field' => 'Lokacija',
                'notification' => [
                    'success' => 'Lokacija ispunjenja ažurirana.',
                    'error' => 'Nije moguće promijeniti lokaciju ispunjenja.',
                ],
            ],
            'return' => [
                'label' => 'Povrat',
                'notification' => [
                    'success' => 'Ispunjenje vraćeno.',
                    'error' => 'Nije moguće vratiti ispunjenje.',
                ],
            ],
            'update_status' => [
                'label' => 'Ažuriraj status',
            ],
            'transition' => [
                'modal_heading' => 'Označiti ispunjenje kao :status?',
                'notification' => [
                    'success' => 'Status ispunjenja ažuriran.',
                    'error' => 'Nije moguće ažurirati status ispunjenja.',
                ],
            ],
            'undo_return' => [
                'label' => 'Poništi povrat',
                'notification' => [
                    'success' => 'Povrat poništen.',
                    'error' => 'Nije moguće poništiti povrat.',
                ],
            ],
            'hold' => [
                'label' => 'Stavi na čekanje',
                'modal_heading' => 'Stavi ispunjenje na čekanje',
                'reason' => 'Razlog',
                'note' => 'Napomena',
                'notification' => [
                    'success' => 'Ispunjenje stavljeno na čekanje.',
                    'error' => 'Nije moguće staviti ispunjenje na čekanje.',
                ],
            ],
            'release' => [
                'label' => 'Oslobodi s čekanja',
                'notification' => [
                    'success' => 'Ispunjenje oslobođeno.',
                    'error' => 'Nije moguće osloboditi ispunjenje.',
                ],
            ],
            'split' => [
                'label' => 'Podijeli',
                'confirm' => 'Podijeli ispunjenje',
                'cancel' => 'Odustani',
                'empty' => 'Odaberite količinu za izdvajanje.',
                'modal_heading' => 'Podijeli ispunjenje',
                'notification' => [
                    'success' => 'Ispunjenje podijeljeno.',
                    'error' => 'Nije moguće podijeliti ispunjenje.',
                ],
            ],
            'merge' => [
                'label' => 'Spoji',
                'confirm' => 'Spoji ispunjenje',
                'cancel' => 'Odustani',
                'modal_heading' => 'Spoji ispunjenje',
                'description' => 'Odaberite stavke koje želite spojiti.',
                'target' => 'Spoji sa',
                'empty' => 'Odaberite stavke i odredište za spajanje.',
                'notification' => [
                    'success' => 'Ispunjenja spojena.',
                    'error' => 'Nije moguće spojiti ispunjenja.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Količina',
            'tracking' => 'Praćenje',
            'tracking_item' => 'Praćenje #:number',
            'unit_price' => 'Jedinična cijena',
            'sub_total' => 'Međuzbroj',
            'discount_total' => 'Ukupan popust',
            'total' => 'Ukupno',
            'stock_level' => 'Trenutna razina zaliha: :count',
            'of' => 'od :count',
            'outstanding' => 'Preostalo: :count',
            'tracking_number' => 'Broj za praćenje',
            'tracking_url' => 'URL za praćenje',
            'carrier' => 'Dostavljač',
            'carrier_custom' => 'Prilagođeno / ostalo',
            'tracking_url_help' => 'Potrebno samo za dostavljače bez automatske poveznice za praćenje.',
            'shipping_method' => 'Način dostave',
            'move_quantity' => 'Količina za premještanje',
        ],
    ],

    'other_items' => [
        'heading' => 'Ostale stavke',
    ],
];
