<?php

return [
    'title' => 'Popusti',
    'description' => 'Postavite akcije koje smanjuju iznos koji kupac plaća — postotak, fiksni iznos ili ponudu kupi jedan, dobij drugi — i odredite kada, gdje i za koga svaka vrijedi.',
    'new_discount' => 'Novi popust',
    'create_title' => 'Novi popust',
    'create_description' => 'Imenujte popust i odaberite kako smanjuje cijenu; sve ostalo podešava se na stranici popusta.',
    'create_discount' => 'Stvori popust',
    'back_to_discounts' => 'Natrag na popuste',
    'delete_discount' => 'Obriši popust',
    'confirm_delete_discount' => 'Obrisati ovaj popust? Košarice koje ga trenutačno koriste bit će ponovno izračunate bez njega.',

    'column_status' => 'Status',
    'column_name' => 'Naziv',
    'column_type' => 'Vrsta',
    'column_coupon' => 'Kupon',
    'column_window' => 'Razdoblje',
    'column_usage' => 'Iskorištenost',
    'column_priority' => 'Prioritet',

    'search_placeholder' => 'Pretraži popuste',
    'filter_status' => 'Status',
    'filter_all_statuses' => 'Svi statusi',
    'filter_type' => 'Vrsta',
    'filter_all_types' => 'Sve vrste',
    'filter_channel' => 'Kanal',
    'filter_all_channels' => 'Svi kanali',
    'filter_customer_group' => 'Grupa kupaca',
    'filter_all_customer_groups' => 'Sve grupe kupaca',
    'filter_redemption' => 'Način primjene',
    'filter_all_redemptions' => 'Kuponski i automatski',
    'redemption_coupon' => 'Zahtijeva kupon',
    'redemption_automatic' => 'Primjenjuje se automatski',
    'sort_priority' => 'Po prioritetu',
    'sort_name' => 'Naziv A-Z',
    'sort_starts' => 'Najranije počinju',
    'sort_ends' => 'Najranije završavaju',
    'sort_uses' => 'Najčešće iskorišteni',
    'count_of' => '{shown} od {total}',
    'clear_filters' => 'Očisti filtre',
    'empty_title' => 'Nema odgovarajućih popusta',
    'empty_description' => 'Očistite pretragu ili filtre ili stvorite novi popust.',
    'empty_none_title' => 'Još nema popusta',
    'empty_none_description' => 'Stvorite prvi popust kako biste pokrenuli akcije.',

    'status_active' => 'Aktivan',
    'status_scheduled' => 'Zakazan',
    'status_expired' => 'Istekao',
    'status_pending' => 'Na čekanju',

    'kpi_active_label' => 'Trenutačno aktivni',
    'kpi_active_hint' => 'Vrijede danas',
    'kpi_scheduled_label' => 'Zakazani',
    'kpi_scheduled_hint' => 'Počinju kasnije',
    'kpi_ending_label' => 'Uskoro završavaju',
    'kpi_ending_hint' => 'Unutar 7 dana',
    'kpi_redemptions_label' => 'Iskorištenja',
    'kpi_redemptions_hint' => 'Svi popusti, od početka',
    'show_kpis' => 'Prikaži statistiku',

    'summary_percentage_off' => ':percentage % popusta',

    'summary_fixed_amount_off' => ':amount popusta',

    'summary_buy_x_get_y' => 'Kupi :buy, dobij :get',

    'field_percentage' => 'Postotak popusta',

    'field_percentage_hint' => 'Oduzima se od svake prihvatljive stavke.',

    'field_amount' => 'Iznos popusta',

    'field_amounts_hint' => 'Postavite iznos po valuti. Valuta ostavljena praznom ne dobiva popust.',

    'field_min_qty' => 'Količina za kupnju',

    'field_reward_qty' => 'Nagradna količina',

    'field_max_reward_qty' => 'Najviše nagrađeno',

    'field_max_reward_qty_hint' => 'Ostavite prazno kako bi se nagradio svaki prihvatljivi komplet.',

    'field_automatically_add_rewards' => 'Automatski dodaj nagrade u košaricu',

    'field_automatically_add_rewards_hint' => 'Dodaje nagradne proizvode umjesto kupca, umjesto da čeka da ih on doda.',

    'section_targets' => 'Odnosi se na',

    'section_targets_description' => 'Suzite ovaj popust na dio kataloga. Prazan blok znači da vrijedi svugdje.',

    'section_customers' => 'Prihvatljivi kupci',

    'bucket_limitation' => 'Odnosi se na',

    'bucket_limitation_description' => 'Samo ovi dobivaju popust.',

    'bucket_exclusion' => 'Isključeno',

    'bucket_exclusion_description' => 'Nikad ne dobivaju popust, čak i ako odgovaraju gornjem.',

    'bucket_condition' => 'Kvalificirajući proizvodi',

    'bucket_condition_description' => 'Što kupac mora kupiti da zaradi nagradu.',

    'bucket_reward' => 'Nagradni proizvodi',

    'bucket_reward_description' => 'Što kupac dobiva.',

    'bucket_customers' => 'Prihvatljivi kupci',

    'bucket_customers_description' => 'Samo ovi kupci mogu iskoristiti popust. Ostavite prazno da vrijedi za sve.',

    'kind_products' => 'Proizvodi',

    'kind_variants' => 'Varijante',

    'kind_collections' => 'Kolekcije',

    'kind_brands' => 'Marke',

    'kind_customers' => 'Kupci',

    'target_add' => 'Dodaj',

    'target_remove' => 'Ukloni {label}',

    'target_empty' => 'Ništa nije odabrano, pa vrijedi za sve.',

    'target_dialog_title' => 'Dodaj ciljeve',

    'target_dialog_description' => 'Pretražite sve što ovaj blok može obuhvatiti.',

    'target_search_placeholder' => 'Pretraži proizvode, kolekcije, marke',

    'target_no_results' => 'Nema podudaranja.',

    'target_add_selected' => 'Dodaj {count}',

    'section_conditions' => 'Uvjeti',

    'section_conditions_description' => 'Što košarica mora zadovoljiti prije nego se ovaj popust primijeni.',

    'field_min_spend' => 'Najmanji iznos',

    'field_min_spend_hint' => 'Postavite prag po valuti. Valuta ostavljena praznom nema minimum.',

    'automatic' => 'Automatski',
    'no_end_date' => 'Bez datuma završetka',
    'usage_unlimited' => 'bez ograničenja',
    'usage_of' => '{used} od {max}',

    'section_details' => 'Detalji',
    'section_details_description' => 'Kako se ovaj popust označava i na kojem je mjestu u redoslijedu primjene.',
    'section_configuration' => 'Konfiguracija',
    'section_configuration_description' => 'Što ovaj popust radi s cijenom.',
    'section_schedule' => 'Raspored',
    'section_usage' => 'Iskorištenost',
    'section_activity' => 'Aktivnost',
    'activity_see_all' => 'Prikaži sve',
    'activity_empty' => 'Još ništa nije zabilježeno.',

    'field_name' => 'Naziv',
    'field_name_create_hint' => 'Vidljiv osoblju. Oznaka se iz njega generira i može se poslije promijeniti.',
    'field_handle' => 'Oznaka',
    'field_handle_hint' => 'Jedinstvena, stalna referenca ovog popusta.',
    'field_type' => 'Vrsta',
    'field_coupon' => 'Kod kupona',
    'field_coupon_hint' => 'Ostavite prazno kako bi se popust primjenjivao automatski.',
    'field_starts_at' => 'Počinje',
    'field_ends_at' => 'Završava',
    'field_ends_at_hint' => 'Ostavite prazno kako bi vrijedio dok ga ne isključite.',
    'field_priority' => 'Prioritet',
    'field_priority_hint' => 'Niža vrijednost primjenjuje se prva. Popusti istog prioriteta primjenjuju se neodređenim redoslijedom.',
    'field_stop' => 'Zaustavi nakon ovog popusta',
    'field_stop_hint' => 'Preskoči sve popuste nižeg prioriteta čim se ovaj primijeni.',
    'field_max_uses' => 'Najveći broj iskorištenja',
    'field_max_uses_hint' => 'Ostavite prazno za neograničeno.',
    'field_max_uses_per_user' => 'Najviše po kupcu',
    'field_max_uses_per_user_hint' => 'Ostavite prazno za neograničeno.',

    'usage_redeemed' => 'Iskorišteno',

    'raw_data_description' => 'Za ovu vrstu popusta u panelu nije registriran obrazac, pa se njezine spremljene postavke ovdje uređuju kao JSON.',
    'raw_data_invalid' => 'Unesite ispravan JSON.',
    'type_missing' => 'Paket koji je registrirao ovu vrstu popusta više nije instaliran.',

    'bulk_end_now' => 'Završi odmah',
    'bulk_delete' => 'Obriši',
    'confirm_bulk_end' => 'Odmah završiti odabrane popuste? Prestaju vrijediti smjesta, ali ostaju na popisu.',
    'confirm_bulk_delete' => 'Obrisati odabrane popuste? Košarice koje ih trenutačno koriste bit će ponovno izračunate bez njih.',

    'flash_created' => 'Popust je stvoren.',
    'flash_updated' => 'Popust je ažuriran.',
    'flash_deleted' => 'Popust je obrisan.',
    'flash_bulk_ended' => 'Završeno je {count} popusta.',
    'flash_bulk_deleted' => 'Obrisano je {count} popusta.',
];
