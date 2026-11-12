<?php

return [
    'title' => 'Rabaty',
    'description' => 'Skonfiguruj promocje obniżające kwotę płaconą przez klienta — procent, kwotę stałą albo ofertę kup jeden, drugi gratis — i określ, kiedy, gdzie i dla kogo każda z nich obowiązuje.',
    'new_discount' => 'Nowy rabat',
    'create_title' => 'Nowy rabat',
    'create_description' => 'Nazwij rabat i wybierz, jak obniża cenę; resztę skonfigurujesz na stronie rabatu.',
    'create_discount' => 'Utwórz rabat',
    'back_to_discounts' => 'Powrót do rabatów',
    'delete_discount' => 'Usuń rabat',
    'confirm_delete_discount' => 'Usunąć ten rabat? Koszyki, które go używają, zostaną przeliczone bez niego.',

    'column_status' => 'Status',
    'column_name' => 'Nazwa',
    'column_type' => 'Typ',
    'column_coupon' => 'Kupon',
    'column_window' => 'Okres',
    'column_usage' => 'Wykorzystanie',
    'column_priority' => 'Priorytet',

    'search_placeholder' => 'Szukaj rabatów',
    'filter_status' => 'Status',
    'filter_all_statuses' => 'Wszystkie statusy',
    'filter_type' => 'Typ',
    'filter_all_types' => 'Wszystkie typy',
    'filter_channel' => 'Kanał',
    'filter_all_channels' => 'Wszystkie kanały',
    'filter_customer_group' => 'Grupa klientów',
    'filter_all_customer_groups' => 'Wszystkie grupy klientów',
    'filter_redemption' => 'Sposób naliczania',
    'filter_all_redemptions' => 'Kuponowe i automatyczne',
    'redemption_coupon' => 'Wymaga kuponu',
    'redemption_automatic' => 'Naliczany automatycznie',
    'sort_priority' => 'Według priorytetu',
    'sort_name' => 'Nazwa A-Z',
    'sort_starts' => 'Najwcześniej zaczynające się',
    'sort_ends' => 'Najwcześniej kończące się',
    'sort_uses' => 'Najczęściej wykorzystywane',
    'count_of' => '{shown} z {total}',
    'clear_filters' => 'Wyczyść filtry',
    'empty_title' => 'Brak pasujących rabatów',
    'empty_description' => 'Wyczyść wyszukiwanie lub filtry albo utwórz nowy rabat.',
    'empty_none_title' => 'Nie ma jeszcze rabatów',
    'empty_none_description' => 'Utwórz pierwszy rabat, aby rozpocząć promocje.',

    'status_active' => 'Aktywny',
    'status_scheduled' => 'Zaplanowany',
    'status_expired' => 'Wygasły',
    'status_pending' => 'Oczekujący',

    'kpi_active_label' => 'Aktywne teraz',
    'kpi_active_hint' => 'Obowiązują dzisiaj',
    'kpi_scheduled_label' => 'Zaplanowane',
    'kpi_scheduled_hint' => 'Zaczynają się później',
    'kpi_ending_label' => 'Wkrótce się kończą',
    'kpi_ending_hint' => 'W ciągu 7 dni',
    'kpi_redemptions_label' => 'Wykorzystania',
    'kpi_redemptions_hint' => 'Wszystkie rabaty, od początku',
    'show_kpis' => 'Pokaż statystyki',

    'summary_percentage_off' => ':percentage% rabatu',

    'summary_fixed_amount_off' => 'Rabat :amount',

    'summary_buy_x_get_y' => 'Kup :buy, otrzymaj :get',

    'field_percentage' => 'Procent rabatu',

    'field_percentage_hint' => 'Odejmowany od każdej kwalifikującej się pozycji.',

    'field_amount' => 'Kwota rabatu',

    'field_amounts_hint' => 'Ustaw kwotę dla każdej waluty. Waluta pozostawiona pusta nie otrzyma rabatu.',

    'field_min_qty' => 'Ilość do kupienia',

    'field_reward_qty' => 'Ilość w nagrodę',

    'field_max_reward_qty' => 'Maksymalna nagroda',

    'field_max_reward_qty_hint' => 'Pozostaw puste, aby nagradzać każdy kwalifikujący się zestaw.',

    'field_automatically_add_rewards' => 'Automatycznie dodawaj nagrody do koszyka',

    'field_automatically_add_rewards_hint' => 'Dodaje produkty nagrody za klienta, zamiast czekać, aż zrobi to sam.',

    'section_targets' => 'Dotyczy',

    'section_targets_description' => 'Ogranicz ten rabat do części katalogu. Pusty blok oznacza, że obowiązuje wszędzie.',

    'section_customers' => 'Uprawnieni klienci',

    'bucket_limitation' => 'Dotyczy',

    'bucket_limitation_description' => 'Tylko te otrzymują rabat.',

    'bucket_exclusion' => 'Wykluczone',

    'bucket_exclusion_description' => 'Nigdy nie otrzymują rabatu, nawet jeśli pasują powyżej.',

    'bucket_condition' => 'Produkty kwalifikujące',

    'bucket_condition_description' => 'Co klient musi kupić, aby otrzymać nagrodę.',

    'bucket_reward' => 'Produkty w nagrodę',

    'bucket_reward_description' => 'Co klient otrzymuje.',

    'bucket_customers' => 'Uprawnieni klienci',

    'bucket_customers_description' => 'Tylko ci klienci mogą użyć rabatu. Pozostaw puste, aby zezwolić wszystkim.',

    'kind_products' => 'Produkty',

    'kind_variants' => 'Warianty',

    'kind_collections' => 'Kolekcje',

    'kind_brands' => 'Marki',

    'kind_customers' => 'Klienci',

    'target_add' => 'Dodaj',

    'target_remove' => 'Usuń {label}',

    'target_empty' => 'Nic nie wybrano, więc dotyczy wszystkiego.',

    'target_dialog_title' => 'Dodaj cele',

    'target_dialog_description' => 'Szukaj wśród wszystkiego, co ten blok może objąć.',

    'target_search_placeholder' => 'Szukaj produktów, kolekcji, marek',

    'target_no_results' => 'Brak dopasowań.',

    'target_add_selected' => 'Dodaj {count}',

    'section_conditions' => 'Warunki',

    'section_conditions_description' => 'Co koszyk musi spełnić, zanim ten rabat zostanie naliczony.',

    'field_min_spend' => 'Minimalna kwota',

    'field_min_spend_hint' => 'Ustaw próg dla każdej waluty. Waluta pozostawiona pusta nie ma minimum.',

    'automatic' => 'Automatyczny',
    'no_end_date' => 'Bez daty zakończenia',
    'usage_unlimited' => 'bez limitu',
    'usage_of' => '{used} z {max}',

    'section_details' => 'Szczegóły',
    'section_details_description' => 'Jak ten rabat jest oznaczony i w którym miejscu kolejności jest naliczany.',
    'section_configuration' => 'Konfiguracja',
    'section_configuration_description' => 'Co ten rabat robi z ceną.',
    'section_schedule' => 'Harmonogram',
    'section_usage' => 'Wykorzystanie',
    'section_activity' => 'Aktywność',
    'activity_see_all' => 'Zobacz wszystko',
    'activity_empty' => 'Nic jeszcze nie zarejestrowano.',

    'field_name' => 'Nazwa',
    'field_name_create_hint' => 'Widoczna dla personelu. Uchwyt jest z niej generowany i można go później zmienić.',
    'field_handle' => 'Uchwyt',
    'field_handle_hint' => 'Unikalne, stałe oznaczenie tego rabatu.',
    'field_type' => 'Typ',
    'field_redemption' => 'Sposób naliczania',
    'field_redemption_hint' => 'Rabat wymagający kuponu obejmuje tylko koszyki z jednym z jego kodów.',
    'field_starts_at' => 'Początek',
    'field_ends_at' => 'Koniec',
    'field_ends_at_hint' => 'Pozostaw puste, aby obowiązywał do momentu wyłączenia.',
    'field_priority' => 'Priorytet',
    'field_priority_hint' => 'Niższa wartość nalicza się wcześniej. Rabaty o tym samym priorytecie naliczają się w nieokreślonej kolejności.',
    'field_stop' => 'Zatrzymaj po tym rabacie',
    'field_stop_hint' => 'Pomiń wszystkie rabaty o niższym priorytecie, gdy ten zostanie naliczony.',
    'field_max_uses' => 'Maksymalna liczba wykorzystań',
    'field_max_uses_hint' => 'Pozostaw puste, aby nie ograniczać.',
    'field_max_uses_per_user' => 'Maksimum na klienta',
    'field_max_uses_per_user_hint' => 'Pozostaw puste, aby nie ograniczać.',

    'usage_redeemed' => 'Wykorzystano',

    'section_coupons' => 'Kupony',
    'section_coupons_description' => 'Kody, którymi można wykorzystać ten rabat. Każdy kod może mieć własny limit wykorzystania.',
    'coupons_search_placeholder' => 'Szukaj kodów i adresów e-mail',
    'coupons_column_code' => 'Kod',
    'coupons_column_uses' => 'Wykorzystania',
    'coupons_column_limit' => 'Maksimum',
    'coupons_column_email' => 'E-mail',
    'coupons_column_batch' => 'Partia',
    'coupons_column_last_redeemed' => 'Ostatnie wykorzystanie',
    'coupons_unlimited' => 'Bez limitu',
    'coupons_never_redeemed' => 'Nigdy',
    'coupons_empty' => 'Brak kodów. Dodaj kod, aby klienci mogli skorzystać z tego rabatu.',
    'coupons_empty_search' => 'Żaden kod nie pasuje do wyszukiwania.',
    'coupons_count' => '{count} kodów',
    'coupons_none' => 'Brak kodów',
    'coupon_add' => 'Dodaj kod',
    'coupon_field_code' => 'Kod',
    'coupon_field_max_uses' => 'Limit wykorzystania',
    'coupon_field_max_uses_hint' => 'Pozostaw puste, aby nie ograniczać.',
    'coupon_delete' => 'Usuń kod',
    'coupon_confirm_delete' => 'Usunąć ten kod? Zamówienia, które go użyły, nadal będą go pokazywać.',
    'coupons_generate' => 'Generuj kody',
    'coupons_generate_description' => 'Utwórz partię unikalnych kodów: określoną liczbę albo jeden dla każdego adresu e-mail.',
    'coupons_generate_source' => 'Kody dla',
    'coupons_generate_source_quantity' => 'Określona liczba kodów',
    'coupons_generate_source_emails' => 'Lista adresów e-mail',
    'coupons_generate_field_quantity' => 'Liczba kodów',
    'coupons_generate_field_emails' => 'Adresy e-mail',
    'coupons_generate_field_emails_hint' => 'Oddziel adresy przecinkami lub nowymi wierszami. Powtórzony adres otrzyma jeden kod.',
    'coupons_generate_field_emails_file' => 'Lub prześlij plik CSV',
    'coupons_generate_field_prefix' => 'Prefiks',
    'coupons_generate_field_prefix_hint' => 'Opcjonalnie. Litery, cyfry, myślniki i podkreślenia.',
    'coupons_generate_field_length' => 'Długość kodu',
    'coupons_generate_field_length_hint' => 'Znaki po prefiksie.',
    'coupons_generate_field_max_uses' => 'Użycia na kod',
    'coupons_generate_background_hint' => 'Ponad {count} kodów jest generowanych w tle.',
    'coupons_generate_submit' => 'Generuj',
    'coupons_generating' => 'Generowanie {quantity} kodów. Gotowe: {generated}.',
    'coupons_generation_failed' => 'Generowanie {quantity} kodów zatrzymało się po {generated}. Utworzone dotąd kody są w tabeli.',
    'coupons_export' => 'Eksportuj do CSV',
    'coupons_export_batch' => 'Eksportuj tę partię do CSV',
    'coupons_generate_invalid_emails' => 'Te adresy e-mail są nieprawidłowe: :emails',
    'coupons_generate_no_emails' => 'Dodaj co najmniej jeden adres e-mail.',
    'coupons_generate_too_many_emails' => 'Generuj maksymalnie :max kodów naraz.',

    'raw_data_description' => 'Ten typ rabatu nie ma zarejestrowanego formularza w panelu, więc jego zapisane ustawienia edytuje się tutaj jako JSON.',
    'raw_data_invalid' => 'Wprowadź poprawny JSON.',
    'type_missing' => 'Pakiet, który zarejestrował ten typ rabatu, nie jest już zainstalowany.',

    'bulk_end_now' => 'Zakończ teraz',
    'bulk_delete' => 'Usuń',
    'confirm_bulk_end' => 'Zakończyć teraz wybrane rabaty? Przestaną obowiązywać natychmiast, ale pozostaną na liście.',
    'confirm_bulk_delete' => 'Usunąć wybrane rabaty? Koszyki, które ich używają, zostaną przeliczone bez nich.',

    'flash_created' => 'Rabat utworzony.',
    'flash_updated' => 'Rabat zaktualizowany.',
    'flash_deleted' => 'Rabat usunięty.',
    'flash_bulk_ended' => 'Zakończono {count} rabatów.',
    'flash_bulk_deleted' => 'Usunięto {count} rabatów.',
    'flash_coupon_added' => 'Kod dodany.',
    'flash_coupon_deleted' => 'Kod usunięty.',
    'flash_coupons_generated' => 'Wygenerowano kody: :count.',
    'flash_coupons_queued' => 'Generowanie kodów w tle: :count.',
];
