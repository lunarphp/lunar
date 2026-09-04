<?php

return [
    'invalid_query' => [
        'title' => 'Nieprawidłowe zapytanie',
    ],
    'query' => [
        'malformed_parameter' => 'Parametr :parameter ma nieprawidłowy format.',
        'unknown_include' => 'Nieznane dołączenie ":value" dla :type. Dozwolone: :allowed.',
        'include_too_deep' => 'Dołączenie ":value" przekracza maksymalną głębokość :max.',
        'unknown_type' => 'Nieznany typ zasobu ":value". Dozwolone: :allowed.',
        'unknown_field' => 'Nieznane pole ":value" dla :type. Dozwolone: :allowed.',
        'unknown_filter' => 'Nieznany filtr ":value". Dozwolone: :allowed.',
        'unknown_operator' => 'Nieznany operator ":value" dla filtru ":filter". Dozwolone: :allowed.',
        'unknown_sort' => 'Nieznane sortowanie ":value". Dozwolone: :allowed.',
        'invalid_page_size' => 'page[size] musi być liczbą całkowitą od 1 do :max.',
        'invalid_page_number' => 'page[number] musi być dodatnią liczbą całkowitą.',
        'cursor_unsupported' => 'Zasób :type nie obsługuje paginacji kursorem.',
        'cursor_and_number' => 'page[cursor] i page[number] nie mogą być użyte razem.',
        'invalid_cursor' => 'page[cursor] nie jest prawidłowym kursorem.',
        'unknown_page_key' => 'Nieznany klucz paginacji ":value". Dozwolone: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Nie znaleziono',
        'detail' => 'Nie istnieje zasób :type o identyfikatorze ":id".',
    ],
    'invalid_header' => [
        'title' => 'Nieprawidłowy nagłówek',
        'detail' => 'Wartość ":value" nagłówka :header nie została rozpoznana.',
    ],
    'invalid_cart_token' => [
        'title' => 'Nieprawidłowy token koszyka',
        'detail' => 'Token X-Lunar-Cart jest nieprawidłowy lub wygasł.',
    ],
    'cart_not_found' => [
        'title' => 'Koszyk nie znaleziony',
        'detail' => 'Koszyk wskazany w X-Lunar-Cart już nie istnieje.',
    ],
    'customer_not_found' => [
        'title' => 'Brak klienta',
        'detail' => 'Uwierzytelniony użytkownik nie ma rekordu klienta.',
    ],
    'validation_failed' => [
        'title' => 'Walidacja nie powiodła się',
    ],
    'unauthenticated' => [
        'title' => 'Nieuwierzytelniony',
        'detail' => 'Wymagane są prawidłowe dane uwierzytelniające.',
    ],
    'forbidden' => [
        'title' => 'Zabronione',
        'detail' => 'Nie masz uprawnień do wykonania tej akcji.',
    ],
    'not_found' => [
        'title' => 'Nie znaleziono',
        'detail' => 'Żądany punkt końcowy lub zasób nie istnieje.',
    ],
    'method_not_allowed' => [
        'title' => 'Metoda niedozwolona',
        'detail' => 'Ten punkt końcowy nie obsługuje tej metody HTTP.',
    ],
    'too_many_requests' => [
        'title' => 'Zbyt wiele żądań',
        'detail' => 'Przekroczono limit żądań. Spróbuj ponownie później.',
    ],
    'server_error' => [
        'title' => 'Błąd serwera',
        'detail' => 'Coś poszło nie tak. Spróbuj ponownie później.',
    ],
];
