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
        'on_hold' => 'In de wacht',
        'empty' => 'Nog geen fulfilments.',
        'columns' => [
            'reference' => 'Referentie',
            'state' => 'Status',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Verzonden op',
            'handed_over' => [
                'shipping' => 'Verzonden op',
                'collection' => 'Afgehaald op',
                'digital' => 'Beschikbaar gesteld op',
            ],
            'handed_over_default' => 'Afgehandeld op',
        ],
        'actions' => [
            'more' => 'Meer acties',
            'notify' => 'Klant op de hoogte stellen',
            'add_tracking' => [
                'label' => 'Tracking toevoegen',
                'modal_heading' => 'Tracking toevoegen',
                'notification' => [
                    'success' => 'Tracking toegevoegd.',
                    'error' => 'Tracking kon niet worden toegevoegd.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Tracking verwijderen',
                'notification' => [
                    'success' => 'Tracking verwijderd.',
                    'error' => 'Tracking kon niet worden verwijderd.',
                ],
            ],
            'create' => [
                'label' => 'Fulfilment aanmaken',
                'modal_heading' => 'Fulfilment aanmaken',
                'empty' => 'Elke regel is al afgehandeld.',
                'notification' => [
                    'success' => 'Fulfilment aangemaakt.',
                    'error' => 'Fulfilment kon niet worden aangemaakt.',
                ],
            ],
            'ship' => [
                'label' => 'Markeren als verzonden',
                'modal_heading' => 'Fulfilment markeren als verzonden',
                'notification' => [
                    'success' => 'Fulfilment gemarkeerd als verzonden.',
                    'error' => 'Fulfilment kon niet worden verzonden.',
                ],
            ],
            'fulfil' => [
                'label' => 'Markeren als afgehandeld',
                'modal_heading' => 'Fulfilment markeren als afgehandeld',
                'labels' => [
                    'collection' => 'Markeren als afgehaald',
                ],
                'notification' => [
                    'success' => 'Fulfilment gemarkeerd als afgehandeld.',
                    'error' => 'Fulfilment kon niet worden afgehandeld.',
                ],
            ],
            'cancel' => [
                'label' => 'Fulfilment annuleren',
                'modal_heading' => 'Fulfilment annuleren',
                'description' => 'Hiermee wordt de fulfilment teruggezet naar in afwachting zodat deze opnieuw kan worden voortgezet. Eventuele verzendgegevens worden gewist.',
                'notification' => [
                    'success' => 'Fulfilment geannuleerd.',
                    'error' => 'Fulfilment kon niet worden geannuleerd.',
                ],
            ],
            'change_location' => [
                'label' => 'Locatie wijzigen',
                'modal_heading' => 'Fulfilment-locatie wijzigen',
                'field' => 'Locatie',
                'notification' => [
                    'success' => 'Fulfilment-locatie bijgewerkt.',
                    'error' => 'De fulfilment-locatie kon niet worden gewijzigd.',
                ],
            ],
            'return' => [
                'label' => 'Retourneren',
                'notification' => [
                    'success' => 'Fulfilment geretourneerd.',
                    'error' => 'Fulfilment kon niet worden geretourneerd.',
                ],
            ],
            'update_status' => [
                'label' => 'Status bijwerken',
            ],
            'transition' => [
                'modal_heading' => 'Fulfilment markeren als :status?',
                'notification' => [
                    'success' => 'Fulfilment-status bijgewerkt.',
                    'error' => 'De fulfilment-status kon niet worden bijgewerkt.',
                ],
            ],
            'undo_return' => [
                'label' => 'Retour ongedaan maken',
                'notification' => [
                    'success' => 'Retour ongedaan gemaakt.',
                    'error' => 'De retour kon niet ongedaan worden gemaakt.',
                ],
            ],
            'hold' => [
                'label' => 'Fulfilment in de wacht zetten',
                'modal_heading' => 'Fulfilment in de wacht zetten',
                'reason' => 'Reden',
                'note' => 'Notitie',
                'notification' => [
                    'success' => 'Fulfilment in de wacht gezet.',
                    'error' => 'De fulfilment kon niet in de wacht worden gezet.',
                ],
            ],
            'release' => [
                'label' => 'Wacht opheffen',
                'notification' => [
                    'success' => 'Fulfilment uit de wacht gehaald.',
                    'error' => 'De fulfilment kon niet uit de wacht worden gehaald.',
                ],
            ],
            'split' => [
                'label' => 'Splitsen',
                'confirm' => 'Fulfilment splitsen',
                'cancel' => 'Annuleren',
                'empty' => 'Selecteer een aantal om af te splitsen.',
                'modal_heading' => 'Fulfilment splitsen',
                'notification' => [
                    'success' => 'Fulfilment gesplitst.',
                    'error' => 'Fulfilment kon niet worden gesplitst.',
                ],
            ],
            'merge' => [
                'label' => 'Samenvoegen',
                'confirm' => 'Fulfilment samenvoegen',
                'cancel' => 'Annuleren',
                'modal_heading' => 'Fulfilment samenvoegen',
                'description' => 'Selecteer de items die je wilt samenvoegen.',
                'target' => 'Samenvoegen met',
                'empty' => 'Selecteer items en een bestemming om samen te voegen.',
                'notification' => [
                    'success' => 'Fulfilments samengevoegd.',
                    'error' => 'Fulfilments konden niet worden samengevoegd.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Aantal',
            'tracking' => 'Tracking',
            'tracking_item' => 'Tracking #:number',
            'unit_price' => 'Stukprijs',
            'sub_total' => 'Subtotaal',
            'discount_total' => 'Totale korting',
            'total' => 'Totaal',
            'stock_level' => 'Huidige voorraad: :count',
            'of' => 'van :count',
            'outstanding' => 'Openstaand: :count',
            'tracking_number' => 'Trackingnummer',
            'tracking_url' => 'Tracking-URL',
            'carrier' => 'Vervoerder',
            'carrier_custom' => 'Aangepast / overig',
            'tracking_url_help' => 'Alleen nodig voor vervoerders zonder automatische trackinglink.',
            'shipping_method' => 'Verzendmethode',
            'move_quantity' => 'Aantal om te verplaatsen',
        ],
    ],

    'other_items' => [
        'heading' => 'Overige items',
    ],
];
