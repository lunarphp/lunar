<?php

return [
    /**
     * Channels.
     */
    'channels.index.title' => 'Canali',
    'channels.index.create_btn' => 'Crea canale',
    'channels.index.table_row_action_text' => 'Modifica canale',
    /**
     * Channels show page.
     */
    'channels.show.title' => 'Modifica canale',
    /**
     * Channels create page.
     */
    'channels.create.title' => 'Crea canale',
    /**
     * Settings layout.
     */
    'layout.menu_btn' => 'Impostazioni',
    /**
     * Staff listing page.
     */
    'staff.index.title' => 'Staff',
    'staff.index.search_placeholder' => 'Cerca staff',
    'staff.index.active_filter' => 'Mostra Inattivo',
    'staff.index.create_btn' => 'Aggiungi Staff',
    'staff.index.table_row_action_text' => 'Modifica staff',
    /**
     * Staff show page.
     */
    'staff.show.title' => 'Modifica Staff',
    'staff.show.delete_btn' => 'Disabilita Account',
    'staff.show.delete_title' => 'Cancella Staff',
    'staff.show.restore_title' => 'Ripristina Staff',
    /**
     * Staff create page.
     */
    'staff.create.title' => 'Crea Staff',
    /**
     * Staff form.
     */
    'staff.form.create_btn' => 'Crea membro dello staff',
    'staff.form.update_btn' => 'Aggiorna membro dello staff',
    'staff.form.permissions_heading' => 'Permessi',
    'staff.form.permissions_description' => 'Gestisci i permessi individuali dei membri dello staff .',
    'staff.form.admin_message' => 'Un utente admin può accedere a tutti i permessi.',
    'staff.form.danger_zone.label' => 'Rimuovi membro dallo staff',
    'staff.form.danger_zone.delete_strapline' => "Rimuovere un membro dallo staff terminerà tutti gli accessi all'hub, ma potrai sempre ripristinarlo successivamente.",
    'staff.form.danger_zone.restore_strapline' => "Ripristina questo membro dello staff, così potrà accedere all'hub.",
    'staff.form.danger_zone.own_account' => 'La rimozione del tuo account, terminerà la sessione.',

    /**
     * Addons listing page.
     */
    'addons.index.title' => 'Addon',
    'addons.index.table_row_action_text' => 'Mostra',
    /**
     * Addons show page.
     */
    'addons.show.title' => 'Addon',
    /*
     * Languages listing page.
     */
    'languages.index.title' => 'Lingue',
    'languages.index.create_btn' => 'Aggiungi Lingua',
    'languages.index.table_row_action_text' => 'Cambia Lingua',
    /**
     * Languages create page.
     */
    'languages.create.title' => 'Aggiungi Lingua',
    /**
     * Languages show page.
     */
    'languages.show.title' => 'Cambia Lingua',
    /**
     * Language form.
     */
    'languages.form.create_btn' => 'Aggiungi Lingua',
    'languages.form.update_btn' => 'Modifica Lingua',
    'languages.form.default_instructions' => 'Scegli se selezionare questa lingua come default, questa operazione sovrascriverà quella corrente.',
    /**
     * Currencies table.
     */
    'currencies.index.title' => 'Valute',
    'currencies.index.table_row_action_text' => 'Modifica',
    'currencies.index.no_results' => 'Attualmente non hai nessuna valuta nel sistema.',
    /**
     * Currency show page.
     */
    'currencies.show.title' => 'Modifica valuta',
    /**
     * Currency create page.
     */
    'currencies.create.title' => 'Aggiungi valuta',
    'currencies.index.create_currency_btn' => 'Aggiungi valuta',
    /**
     * Currency form.
     */
    'currencies.form.update_btn' => 'Modifica valuta',
    'currencies.form.create_btn' => 'Aggiungi valuta',
    'currencies.form.notify.created' => 'Valuta aggiunta',
    'currencies.form.format_help_text' => [
        "Quest'azione ti permetterà di specificare il formato del campo della valuta da dover utilizzare",
        'Quando mostrato, Lunar sostituirà <code>{value}</code> col prezzo formattato. Es.: <code>€{value}</code>.',
        '<code>{value}</code> deve essere sempre incluso per poter funzionare correttamente.',
    ],
    /**
     * Attributes.
     */
    'attributes.index.title' => 'Attributi',
    'attributes.show.title' => 'Modifica gli attributi di :type',
    'attributes.show.locked' => 'Questo attributo è richiesto dal sistema, pertanto le modifica sono bloccate.',
    'attributes.create.title' => 'Crea Attributo ',
    'attributes.form.update_btn' => 'Modifica Attributo',
    'attributes.form.create_btn' => 'Crea Attributo',
    'attributes.form.notify.created' => 'Attributo creato',
    /**
     * Tags.
     */
    'tags.show.title' => 'Modifica Tag',
    'tags.index.title' => 'Tags',
    'tags.index.table_row_action_text' => 'Modifica',
    'tags.form.update_btn' => 'Modifica Tag',
    'tags.form.create_btn' => 'Crea Tag',
    'tags.form.notify.updated' => 'Tag modificato',
    /**
     * Activity log page.
     */
    'activity_log.index.title' => 'Log delle attività',
    /*
     * Product Options
     */
    'product.options.index.title' => 'Opzioni',
    'product.options.index.create_btn' => 'Crea Opzioni',
    'product.options.index.table_row_action_text' => 'Modifica Opzioni',
    /**
     * Taxes.
     */
    'taxes.tax-zones.index.title' => 'Zone Fiscali',
    'taxes.tax-zones.confirm_delete.title' => 'Conferma rimozione',
    'taxes.tax-zones.confirm_delete.message' => 'Sei sicuro di voler rimuovere questa Zona Fiscale?, perderai i dati salvati.',
    'taxes.tax-zones.customer_groups.title' => 'Limita ad un gruppo di clienti',
    'taxes.tax-zones.customer_groups.instructions' => 'Selezione a quale gruppo di clienti vuoi applicare le restrizioni. Deseleziona il box se non vuoi applicare le restrizioni. ',
    'taxes.tax-zones.create_title' => 'Crea zona fiscale',
    'taxes.tax-zones.create_btn' => 'Crea zona fiscale',
    'taxes.tax-zones.delete_btn' => 'Cancella zona fiscale',
    'taxes.tax-zones.index.table_row_action_text' => 'Gestisci',
    'taxes.tax-classes.index.title' => 'Categorie Fiscali',
    'taxes.tax-classes.index.create.title' => 'Crea una categoria fiscale',
    'taxes.tax-classes.index.update.title' => 'Modifica una categoria fiscale',
    'taxes.tax-classes.create_btn' => 'Crea una categoria fiscale',
    'taxes.tax-zones.price_display.label' => 'Mostra prezzo',
    'taxes.tax-zones.price_display.excl_tax' => 'Escludi tasse',
    'taxes.tax-zones.price_display.incl_tax' => 'Includi tasse',
    'taxes.tax-zones.zone_type.label' => 'Tipo',
    'taxes.tax-zones.zone_type.countries' => 'Limita per nazione',
    'taxes.tax-zones.zone_type.states' => 'Limita per stato/provincia',
    'taxes.tax-zones.zone_type.postcodes' => 'Limita per codice postale',
    'taxes.tax-zones.tax_rates.title' => 'Aliquote Fiscali',
    'taxes.tax-zones.tax_rates.create_button' => 'Aggiungi aliquota fiscale',
    'taxes.tax-zones.save_btn' => 'Salva aliquota fiscale',
    'taxes.tax-classes.index.delete_message' => 'Sei sicuro? potresti perdere i dati salvati.',
    'taxes.tax-classes.index.delete_message_disabled' => 'Non puoi rimuovere un aliquota fiscale che non è associata a nessuna variante di prodotto',
    'taxes.tax-classes.index.delete_message_default' => 'Devi selezionare uno nuovo prima di cancellare',
    /**
     * Customer Groups.
     */
    'customer-groups.index.title' => 'Gruppo di clienti',
    'customer-groups.index.create_btn' => 'Aggiungi gruppo di clienti',
    'customer-groups.index.table_row_action_text' => 'Modifica gruppo',
    /**
     * Customer Groups show page.
     */
    'customer-groups.show.title' => 'Modifica gruppo di clienti',
    /**
     * Customer Groups create page.
     */
    'customer-groups.create.title' => 'Crea un gruppo di clienti',
    'customer-groups.form.default_instructions' => 'Scegli se questo gruppo di clienti è impostato come default',
];
