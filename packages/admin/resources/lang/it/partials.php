<?php

return [
    /**
     * Activity Log.
     */
    'activity-log.added_images.description' => 'Aggiunte :count immagini',
    'activity-log.system.system' => 'Sistema',
    'activity-log.update.updated' => 'Aggiornato',
    'activity-log.create.created' => 'Creato',
    /**
     * Associations.
     */
    'products.associations.heading' => 'Associazioni',
    'products.associations.cross-sell' => 'Cross Sell', // TODO check use
    'products.associations.up-sell' => 'Up Sell', // TODO check use
    'products.associations.alternate' => 'Alterna', // TODO check use
    'products.associations.show_inverse' => 'Mostra inversa',
    'products.associations.add_inverse' => 'Aggiungi associazione inversa',
    'products.associations.add_association' => 'Aggiungi associazione',
    'products.associations.up-sell_selecting_products' => "Aggiungi i prodotti per l'Up Sell cercandoli qui su e selezionandoli.",
    /**
     * Availability.
     */
    'availability.heading' => 'Disponibilità',
    'availability.schedule_notice' => "Quando programmi una disponibilità, questo :type non sarà disponibile per il canale/gruppo di clienti finché la data non sarà passata e :type non sarà nuovamente attivo",
    'availability.channel_heading' => 'Canali',
    'availability.channel_strapline' => 'Seleziona su quale canale questo :type è disponibile.',
    'availability.channels.hidden' => 'Nascosto',
    'availability.channels.purchasable' => 'Acquistabile',
    'availability.channels.strapline' => 'Programma per quale gruppo di clienti questo :type di prodotto è disponibile.',
    'availability.channels.scheduled_from' => 'Pianificato per :datetime',
    'availability.channels.scheduled_to' => 'Disponibile fino :datetime',
    'availability.channels.scheduled_range' => ':from fino a :to',
    'availability.channels.scheduled_always' => 'Disponibile',
    'availability.channels.scheduled_never' => 'Non disponibile',
    'availability.channels.schedule_modal.title' => 'Pianifica disponibilità',
    'availability.channels.schedule_modal.starts_at.label' => 'Comincia il',
    'availability.channels.schedule_modal.starts_at.instructions' => 'Seleziona da quando questo gruppo di clienti sarà disponibile, nessuna data indica che è sempre disponibile.',
    'availability.channels.schedule_modal.ends_at.label' => 'Finisce il',
    'availability.channels.schedule_modal.ends_at.instructions' => 'Seleziona fino a quando questo gruppo di clienti sarà disponibile, nessuna data indica che è sempre disponibile.',
    'availability.channels.schedule_modal.btn_text' => 'Accetta & Chiudi',
    'availability.scheduled_text' => 'This :type is scheduled to be published on :date.',
    'availability.schedule_placeholder' => 'Pianifica data di pubblicazione.',
    'availability.schedule_btn_text' => 'Pianifica disponibilità',
    'availability.clear_btn' => 'Pulisci',
    'availability.customer_groups.title' => 'Gruppi di clienti',
    'availability.customer_groups.visible' => 'Visibile',
    'availability.customer_groups.hidden' => 'Nascosto',
    'availability.customer_groups.purchasable' => 'Acquistabile',
    'availability.customer_groups.strapline' => 'Pianifica per quale gruppo di clienti questo :type sarà disponibile.',
    'availability.customer_groups.scheduled_from' => 'Pianificato dal :datetime',
    'availability.customer_groups.scheduled_to' => 'Disponibile fino al :datetime',
    'availability.customer_groups.scheduled_range' => ':from al :to',
    'availability.customer_groups.scheduled_always' => 'Disponibile',
    'availability.customer_groups.scheduled_never' => 'Non disponibile',
    'availability.customer_groups.schedule_modal.title' => 'Pianifica disponibilità',
    'availability.customer_groups.schedule_modal.starts_at.label' => 'Inizia il',
    'availability.customer_groups.schedule_modal.starts_at.instructions' => 'Seleziona da quando questo gruppo di clienti sarà disponibile, nessuna data indica che è sempre disponibile.',
    'availability.customer_groups.schedule_modal.ends_at.label' => 'Finisce il',
    'availability.customer_groups.schedule_modal.ends_at.instructions' => 'Seleziona fino a quando questo gruppo di clienti sarà disponibile, nessuna data indica che è sempre disponibile.',
    'availability.customer_groups.schedule_modal.btn_text' => 'Accetta & Chiudi',
    /**
     * Basic Information.
     */
    'products.basic-information.heading' => 'Informazioni generali',
    /**
     * Image Manager.
     */
    'image-manager.generic_upload_error' => 'Si è verificato un problema durante il caricamento, verifica di aver selezionato solo immagini.',
    'image-manager.heading' => 'Immagini',
    'image-manager.download_original_btn' => 'Scarica Originale',
    'image-manager.remake_transforms' => 'Riesegui Trasformazioni',
    'image-manager.remake_transforms.notify.success' => 'Le trasformazioni alle immagini sono state rigenerate',
    'image-manager.save_btn' => 'Salva immagine',
    'image-manager.edit_row_btn' => 'Modifica',
    'image-manager.delete_row_btn' => 'Elimina',
    'image-manager.delete_primary' => "Non puoi eliminare l'immagine privata.",
    'image-manager.delete_message' => "Quest'immagine verrà eliminata al salvataggio,",
    'image-manager.undo_btn' => 'annulla',
    'image-manager.no_results' => "Nessun'immagine esistente per questo prodotto, aggiungi la prima qui su.",
    'image-manager.upload_file' => 'Carica un file o trascinalo qui',
    'image-manager.file_format' => 'PNG, JPG, GIF fino a 10MB',
    'image-manager.select_images' => 'Seleziona immagini',
    'image-manager.select_images_btn' => 'Seleziona immagini',
    /**
     * Discounts
     */
    'discounts.limitations.heading' => 'Limitazioni',
    'discounts.limitations.by_collection' => 'Limita per collezione',
    'discounts.limitations.by_brand' => 'Limita per marchio',
    'discounts.limitations.by_product' => 'Limita per prodotto',
    'discounts.limitations.view_brand' => 'Mostra Marchio',
    'discounts.limitations.view_product' => 'Mostra Prodotto',

    /**
     * Product Collections.
     */
    'products.collections.heading' => 'Collezioni',
    'products.collections.view_collection' => 'Mostra Collezioni',
    /**
     * Product Status Bar.
     */
    'products.status-bar.published.label' => 'Pubblicato',
    'products.status-bar.published.description' => 'Questo prodotto sarà disponibile per tutti i canali abilitati e a tutti i gruppi di clienti.',
    'products.status-bar.draft.label' => 'Bozza',
    'products.status-bar.draft.description' => 'Questo prodotto verrà nascosto a tutti i canali abilitati e a tutti i gruppi di clienti.',
    /**
     * Variants.
     */
    'products.variants.heading' => 'Varianti',
    'products.variants.strapline' => 'Questo prodotto ha varie opzioni, come dimensioni diverse o colori.',
    'products.variants.table_row_action_text' => 'Modifica',
    'products.variants.table_row_delete_text' => 'Elimina',
    'products.variants.removal_message' => "Questa operazione rimuoverà tutte le varianti da questo prodotto",
    /**
     * Product type.
     */
    'product-type.available_title' => 'Attributi Disponibili',
    'product-type.selected_title' => 'Seleziona Attributi (:count)',
    'product-type.attribute_search_placeholder' => 'Cerca un attributo per nome',
    'product-type.attribute_system_required' => 'Questo attributo è richiesto dal sistema',
    'product-type.product_attributes_btn' => 'Attributi dei prodotti',
    'product-type.variant_attributes_btn' => 'Attributi delle varianti',
    /**
     * Pricing.
     */
    'pricing.title' => 'Prezzi',
    'pricing.customer_groups.title' => 'Prezzi per gruppo di clienti',
    'pricing.customer_groups.strapline' => 'Determina se vuoi prezzi diversi per i gruppi di clienti.',
    'pricing.tiers.title' => 'Prezzi scaglionati',
    'pricing.tiers.strapline' => 'I prezzi scaglionati ti permettono di offrire degli sconti basati sulle unità vendute.',
    'pricing.non_default_currency_alert' => 'Alcuni campi possono essere cambiati soltanto quando vi è una valuta predefinita.',
    'pricing.tiers.add_tier_btn' => 'Aggiungi scaglione',
    /**
     * Indentifiers.
     */
    'identifiers.title' => 'Identificatori prodotto',
    /**
     * URLs.
     */
    'urls.title' => 'URLs',
    'urls.create_btn' => 'Aggiungi URL',
    /**
     * Inventory.
     */
    'inventory.title' => 'Inventario',
    'inventory.maintenance_notice' => ' Questa sezione è ancora sotto sviluppo, cambierà nei prossimi aggiornamenti.',
    'inventory.options.in_stock' => 'In magazzino',
    'inventory.options.always' => 'Sempre',
    'inventory.options.backorder' => 'Backorder', // TODO: translate backorder.
    'inventory.purchasable.in_stock' => "Quest'articolo può essere acquistato quando sarà disponibile in magazzino.",
    'inventory.purchasable.always' => "Quest'articolo è sempre disponibile.",
    'inventory.purchasable.backorder' => "Quest'articolo può essere acquistato a partire dal pre ordine in magazzino.",
    /**
     * Shipping.
     */
    'shipping.title' => 'Consegna',
    'shipping.calculated_volume' => 'Calcolato come :value.',
    'shipping.manual_volume_btn' => 'Clicca per aggiungerlo manualmente',
    'shipping.auto_volume_btn' => 'Usa volume generato',
    /**
     * Customer Addresses.
     */
    'customers.addresses.billing_default' => 'Fatturazione predefinita',
    'customers.addresses.shipping_default' => 'Consegna predefinita',
    /**
     * Customers.
     */
    'customers.purchase-history.purchasable' => 'Acquistabile',
    'customers.purchase-history.identifier' => 'Identificatore',
    'customers.purchase-history.quantity' => 'Quantità',
    'customers.purchase-history.revenue' => 'Entrata',
    'customers.purchase-history.order_count' => 'Numero ordini',
    'customers.purchase-history.last_ordered' => 'Ultimo ordine',
    /**
     * Orders.
     */
    'orders.totals.sub_total' => 'Sub Totale',
    'orders.totals.shipping_total' => 'Totale consegna',
    'orders.totals.total' => 'Totale',
    'orders.totals.notes_empty' => 'Non ci sono note per questo ordine',
    'orders.totals.discount_total' => 'Sconto totale',
    'orders.lines.unit_price' => 'Quantità',
    'orders.lines.sub_total' => 'Sub Totale',
    'orders.lines.discount_total' => 'Sconto totale',
    'orders.lines.total' => 'Totale',
    'orders.lines.current_stock_level' => 'Livello corrente in magazzino: :count',
    'orders.lines.purchase_stock_level' => "Tempo corrente in stock: :count",
    'orders.details.status' => 'Stato',
    'orders.details.reference' => 'Referenza',
    'orders.details.customer_reference' => 'Referenze cliente',
    'orders.details.channel' => 'Canale',
    'orders.details.date_created' => 'Data creata',
    'orders.details.date_placed' => 'Data effettua',
    'orders.details.new_returning' => 'Nuovo/Ricorrente',
    'orders.details.new_customer' => 'Nuovo cliente',
    'orders.details.returning_customer' => 'Cliente ricorrente',
    'orders.address.not_set' => 'Nessun indirizzo impostato',
    /**
     * Forms.
     */
    'forms.channel.delete_channel' => 'Cancella canale',
    'forms.channel.channel_name_delete' => 'Digita il nome del canale da cancellare',
    'forms.brand_delete_brand' => 'Rimuovi brand',
    'forms.brand_name_delete' => 'Digita il nome del brand da cancellare',
    'forms.customer-group.delete_customer_group' => 'Rimuovi gruppo clienti',
    'forms.customer-group.customer_group_name_delete' => 'Digita il nome del gruppo di clienti da cancellare',
    /**
     * Transactions.
     */
    'orders.transactions.capture' => 'Catturato',
    'orders.transactions.intent' => 'Intento',
    'orders.transactions.refund' => 'Rimborsato',
];
