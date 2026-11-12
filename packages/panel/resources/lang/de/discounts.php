<?php

return [
    'title' => 'Rabatte',
    'description' => 'Richten Sie die Aktionen ein, die den Preis für Kunden senken — ein Prozentsatz, ein fester Betrag oder ein Kaufe-eins-erhalte-eins-Angebot — und legen Sie fest, wann, wo und für wen sie gelten.',
    'new_discount' => 'Neuer Rabatt',
    'create_title' => 'Neuer Rabatt',
    'create_description' => 'Benennen Sie den Rabatt und wählen Sie, wie er den Preis senkt; alles Weitere wird auf der Rabattseite konfiguriert.',
    'create_discount' => 'Rabatt erstellen',
    'back_to_discounts' => 'Zurück zu den Rabatten',
    'delete_discount' => 'Rabatt löschen',
    'confirm_delete_discount' => 'Diesen Rabatt löschen? Warenkörbe, die ihn derzeit nutzen, werden ohne ihn neu berechnet.',

    'column_status' => 'Status',
    'column_name' => 'Name',
    'column_type' => 'Typ',
    'column_coupon' => 'Gutschein',
    'column_window' => 'Laufzeit',
    'column_usage' => 'Nutzung',
    'column_priority' => 'Priorität',

    'search_placeholder' => 'Rabatte suchen',
    'filter_status' => 'Status',
    'filter_all_statuses' => 'Alle Status',
    'filter_type' => 'Typ',
    'filter_all_types' => 'Alle Typen',
    'filter_channel' => 'Kanal',
    'filter_all_channels' => 'Alle Kanäle',
    'filter_customer_group' => 'Kundengruppe',
    'filter_all_customer_groups' => 'Alle Kundengruppen',
    'filter_redemption' => 'Einlösung',
    'filter_all_redemptions' => 'Gutschein und automatisch',
    'redemption_coupon' => 'Benötigt einen Gutschein',
    'redemption_automatic' => 'Wird automatisch angewendet',
    'sort_priority' => 'Nach Priorität',
    'sort_name' => 'Name A-Z',
    'sort_starts' => 'Beginnt am ehesten',
    'sort_ends' => 'Endet am ehesten',
    'sort_uses' => 'Am häufigsten eingelöst',
    'count_of' => '{shown} von {total}',
    'clear_filters' => 'Filter zurücksetzen',
    'empty_title' => 'Keine passenden Rabatte',
    'empty_description' => 'Setzen Sie die Suche oder die Filter zurück oder erstellen Sie einen neuen Rabatt.',
    'empty_none_title' => 'Noch keine Rabatte',
    'empty_none_description' => 'Erstellen Sie Ihren ersten Rabatt, um mit Aktionen zu starten.',

    'status_active' => 'Aktiv',
    'status_scheduled' => 'Geplant',
    'status_expired' => 'Abgelaufen',
    'status_pending' => 'Ausstehend',

    'kpi_active_label' => 'Jetzt aktiv',
    'kpi_active_hint' => 'Läuft heute',
    'kpi_scheduled_label' => 'Geplant',
    'kpi_scheduled_hint' => 'Beginnt später',
    'kpi_ending_label' => 'Endet bald',
    'kpi_ending_hint' => 'Innerhalb von 7 Tagen',
    'kpi_redemptions_label' => 'Einlösungen',
    'kpi_redemptions_hint' => 'Alle Rabatte, gesamter Zeitraum',
    'show_kpis' => 'Statistiken anzeigen',

    'summary_percentage_off' => ':percentage % Rabatt',

    'summary_fixed_amount_off' => ':amount Rabatt',

    'summary_buy_x_get_y' => 'Kaufe :buy, erhalte :get',

    'field_percentage' => 'Rabatt in Prozent',

    'field_percentage_hint' => 'Wird von jeder berechtigten Position abgezogen.',

    'field_amount' => 'Rabattbetrag',

    'field_amounts_hint' => 'Legen Sie je Währung einen Betrag fest. Eine leer gelassene Währung erhält keinen Rabatt.',

    'field_min_qty' => 'Zu kaufende Menge',

    'field_reward_qty' => 'Belohnte Menge',

    'field_max_reward_qty' => 'Maximal belohnt',

    'field_max_reward_qty_hint' => 'Leer lassen, um jeden qualifizierenden Satz zu belohnen.',

    'field_automatically_add_rewards' => 'Belohnungen automatisch in den Warenkorb legen',

    'field_automatically_add_rewards_hint' => 'Legt die Belohnungsprodukte für den Kunden hinein, statt darauf zu warten, dass er es tut.',

    'section_targets' => 'Gilt für',

    'section_targets_description' => 'Grenzen Sie diesen Rabatt auf einen Teil des Katalogs ein. Ein leerer Block gilt überall.',

    'section_customers' => 'Berechtigte Kunden',

    'bucket_limitation' => 'Gilt für',

    'bucket_limitation_description' => 'Nur diese werden rabattiert.',

    'bucket_exclusion' => 'Ausgeschlossen',

    'bucket_exclusion_description' => 'Werden nie rabattiert, auch wenn sie oben zutreffen.',

    'bucket_condition' => 'Qualifizierende Produkte',

    'bucket_condition_description' => 'Was der Kunde kaufen muss, um die Belohnung zu erhalten.',

    'bucket_reward' => 'Belohnungsprodukte',

    'bucket_reward_description' => 'Was der Kunde bekommt.',

    'bucket_customers' => 'Berechtigte Kunden',

    'bucket_customers_description' => 'Nur diese Kunden können den Rabatt nutzen. Leer lassen, um ihn allen zu erlauben.',

    'kind_products' => 'Produkte',

    'kind_variants' => 'Varianten',

    'kind_collections' => 'Kollektionen',

    'kind_brands' => 'Marken',

    'kind_customers' => 'Kunden',

    'target_add' => 'Hinzufügen',

    'target_remove' => '{label} entfernen',

    'target_empty' => 'Nichts ausgewählt, gilt also für alles.',

    'target_dialog_title' => 'Ziele hinzufügen',

    'target_dialog_description' => 'Suchen Sie über alles, was dieser Block erfassen kann.',

    'target_search_placeholder' => 'Produkte, Kollektionen, Marken suchen',

    'target_no_results' => 'Keine Treffer.',

    'target_add_selected' => '{count} hinzufügen',

    'section_conditions' => 'Bedingungen',

    'section_conditions_description' => 'Was ein Warenkorb erfüllen muss, bevor dieser Rabatt greift.',

    'field_min_spend' => 'Mindestbestellwert',

    'field_min_spend_hint' => 'Legen Sie je Währung eine Schwelle fest. Eine leer gelassene Währung hat keinen Mindestwert.',

    'automatic' => 'Automatisch',
    'no_end_date' => 'Kein Enddatum',
    'usage_unlimited' => 'unbegrenzt',
    'usage_of' => '{used} von {max}',

    'section_details' => 'Details',
    'section_details_description' => 'Wie dieser Rabatt benannt ist und an welcher Stelle er in der Reihenfolge greift.',
    'section_configuration' => 'Konfiguration',
    'section_configuration_description' => 'Was dieser Rabatt mit dem Preis macht.',
    'section_schedule' => 'Zeitplan',
    'section_usage' => 'Nutzung',
    'section_activity' => 'Aktivität',
    'activity_see_all' => 'Alle anzeigen',
    'activity_empty' => 'Noch nichts aufgezeichnet.',

    'field_name' => 'Name',
    'field_name_create_hint' => 'Wird Mitarbeitern angezeigt. Das Handle wird daraus erzeugt und kann danach geändert werden.',
    'field_handle' => 'Handle',
    'field_handle_hint' => 'Eine eindeutige, dauerhafte Referenz für diesen Rabatt.',
    'field_type' => 'Typ',
    'field_redemption' => 'Einlösung',
    'field_redemption_hint' => 'Ein Rabatt, der einen Gutschein erfordert, gilt nur für Warenkörbe mit einem seiner Codes.',
    'field_starts_at' => 'Beginnt',
    'field_ends_at' => 'Endet',
    'field_ends_at_hint' => 'Leer lassen, damit er läuft, bis Sie ihn abschalten.',
    'field_priority' => 'Priorität',
    'field_priority_hint' => 'Niedrigere Werte greifen zuerst. Rabatte gleicher Priorität greifen in unbestimmter Reihenfolge.',
    'field_stop' => 'Nach diesem Rabatt stoppen',
    'field_stop_hint' => 'Alle Rabatte niedrigerer Priorität überspringen, sobald dieser greift.',
    'field_max_uses' => 'Maximale Einlösungen',
    'field_max_uses_hint' => 'Für unbegrenzt leer lassen.',
    'field_max_uses_per_user' => 'Maximum je Kunde',
    'field_max_uses_per_user_hint' => 'Für unbegrenzt leer lassen.',

    'usage_redeemed' => 'Eingelöst',

    'section_coupons' => 'Gutscheine',
    'section_coupons_description' => 'Die Codes, mit denen dieser Rabatt eingelöst wird. Jeder Code kann ein eigenes Nutzungslimit haben.',
    'coupons_search_placeholder' => 'Codes und E-Mail-Adressen suchen',
    'coupons_column_code' => 'Gutscheincode',
    'coupons_column_uses' => 'Einlösungen',
    'coupons_column_limit' => 'Obergrenze',
    'coupons_column_email' => 'E-Mail',
    'coupons_column_batch' => 'Charge',
    'coupons_column_last_redeemed' => 'Zuletzt eingelöst',
    'coupons_unlimited' => 'Unbegrenzt',
    'coupons_never_redeemed' => 'Nie',
    'coupons_empty' => 'Noch keine Codes. Fügen Sie einen hinzu, damit Kunden diesen Rabatt einlösen können.',
    'coupons_empty_search' => 'Keine Codes entsprechen Ihrer Suche.',
    'coupons_count' => '{count} Codes',
    'coupons_none' => 'Keine Codes',
    'coupon_add' => 'Code hinzufügen',
    'coupon_field_code' => 'Gutscheincode',
    'coupon_field_max_uses' => 'Nutzungslimit',
    'coupon_field_max_uses_hint' => 'Für unbegrenzt leer lassen.',
    'coupon_delete' => 'Code löschen',
    'coupon_confirm_delete' => 'Diesen Code löschen? Bestellungen, die ihn verwendet haben, zeigen ihn weiterhin an.',
    'coupons_generate' => 'Codes generieren',
    'coupons_generate_description' => 'Erstellen Sie einen Satz eindeutiger Codes, entweder eine feste Anzahl oder einen pro E-Mail-Adresse.',
    'coupons_generate_source' => 'Codes für',
    'coupons_generate_source_quantity' => 'Eine Anzahl von Codes',
    'coupons_generate_source_emails' => 'Eine Liste von E-Mails',
    'coupons_generate_field_quantity' => 'Anzahl der Codes',
    'coupons_generate_field_emails' => 'E-Mail-Adressen',
    'coupons_generate_field_emails_hint' => 'Trennen Sie Adressen durch Kommas oder Zeilenumbrüche. Eine doppelte Adresse erhält einen Code.',
    'coupons_generate_field_emails_file' => 'Oder eine CSV-Datei hochladen',
    'coupons_generate_field_prefix' => 'Präfix',
    'coupons_generate_field_prefix_hint' => 'Optional. Buchstaben, Ziffern, Bindestriche und Unterstriche.',
    'coupons_generate_field_length' => 'Codelänge',
    'coupons_generate_field_length_hint' => 'Zeichen nach dem Präfix.',
    'coupons_generate_field_max_uses' => 'Nutzungen pro Code',
    'coupons_generate_background_hint' => 'Mehr als {count} Codes werden im Hintergrund generiert.',
    'coupons_generate_submit' => 'Generieren',
    'coupons_generating' => '{quantity} Codes werden generiert. Bisher fertig: {generated}.',
    'coupons_generation_failed' => 'Die Generierung von {quantity} Codes wurde nach {generated} abgebrochen. Die bisher erstellten Codes stehen in der Tabelle.',
    'coupons_export' => 'Als CSV exportieren',
    'coupons_export_batch' => 'Diese Charge als CSV exportieren',
    'coupons_generate_invalid_emails' => 'Dies sind keine gültigen E-Mail-Adressen: :emails',
    'coupons_generate_no_emails' => 'Fügen Sie mindestens eine E-Mail-Adresse hinzu.',
    'coupons_generate_too_many_emails' => 'Generieren Sie höchstens :max Codes auf einmal.',

    'raw_data_description' => 'Für diesen Rabatttyp ist im Panel kein Formular registriert, daher werden seine gespeicherten Einstellungen hier als JSON bearbeitet.',
    'raw_data_invalid' => 'Geben Sie gültiges JSON ein.',
    'type_missing' => 'Das Paket, das diesen Rabatttyp registriert hat, ist nicht mehr installiert.',

    'bulk_end_now' => 'Jetzt beenden',
    'bulk_delete' => 'Löschen',
    'confirm_bulk_end' => 'Die ausgewählten Rabatte jetzt beenden? Sie greifen sofort nicht mehr, bleiben aber in der Liste.',
    'confirm_bulk_delete' => 'Die ausgewählten Rabatte löschen? Warenkörbe, die sie derzeit nutzen, werden ohne sie neu berechnet.',

    'flash_created' => 'Rabatt erstellt.',
    'flash_updated' => 'Rabatt aktualisiert.',
    'flash_deleted' => 'Rabatt gelöscht.',
    'flash_bulk_ended' => '{count} Rabatte beendet.',
    'flash_bulk_deleted' => '{count} Rabatte gelöscht.',
    'flash_coupon_added' => 'Code hinzugefügt.',
    'flash_coupon_deleted' => 'Code gelöscht.',
    'flash_coupons_generated' => ':count Codes generiert.',
    'flash_coupons_queued' => ':count Codes werden im Hintergrund generiert.',
];
