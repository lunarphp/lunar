<?php

return [

    'label' => 'Bestelling',

    'plural_label' => 'Bestellingen',

    'breadcrumb' => [
        'manage' => 'Beheren',
    ],

    'tabs' => [
        'all' => 'Alle',
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
            'label' => 'Status',
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
            'label' => 'Status',
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

];
