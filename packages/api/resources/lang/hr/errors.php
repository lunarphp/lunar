<?php

return [
    'invalid_query' => [
        'title' => 'Nevažeći upit',
    ],
    'query' => [
        'malformed_parameter' => 'Parametar :parameter je pogrešno oblikovan.',
        'unknown_include' => 'Nepoznato uključivanje ":value" na :type. Dopušteno: :allowed.',
        'include_too_deep' => 'Uključivanje ":value" prelazi najveću dubinu od :max.',
        'unknown_type' => 'Nepoznat tip resursa ":value". Dopušteno: :allowed.',
        'unknown_field' => 'Nepoznato polje ":value" na :type. Dopušteno: :allowed.',
        'unknown_filter' => 'Nepoznat filter ":value". Dopušteno: :allowed.',
        'unknown_operator' => 'Nepoznat operator ":value" za filter ":filter". Dopušteno: :allowed.',
        'unknown_sort' => 'Nepoznato sortiranje ":value". Dopušteno: :allowed.',
        'invalid_page_size' => 'page[size] mora biti cijeli broj između 1 i :max.',
        'invalid_page_number' => 'page[number] mora biti pozitivan cijeli broj.',
        'cursor_unsupported' => 'Resurs :type ne podržava straničenje pokazivačem.',
        'cursor_and_number' => 'page[cursor] i page[number] ne mogu se kombinirati.',
        'invalid_cursor' => 'page[cursor] nije važeći pokazivač.',
        'unknown_page_key' => 'Nepoznat ključ straničenja ":value". Dopušteno: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Nije pronađeno',
        'detail' => 'Ne postoji resurs :type s id-om ":id".',
    ],
    'invalid_header' => [
        'title' => 'Nevažeće zaglavlje',
        'detail' => 'Vrijednost ":value" zaglavlja :header nije prepoznata.',
    ],
    'invalid_cart_token' => [
        'title' => 'Nevažeći token košarice',
        'detail' => 'Token X-Lunar-Cart je nevažeći ili je istekao.',
    ],
    'cart_not_found' => [
        'title' => 'Košarica nije pronađena',
        'detail' => 'Košarica na koju upućuje X-Lunar-Cart više ne postoji.',
    ],
    'customer_not_found' => [
        'title' => 'Nema kupca',
        'detail' => 'Prijavljeni korisnik nema zapis kupca.',
    ],
    'validation_failed' => [
        'title' => 'Provjera nije uspjela',
    ],
    'unauthenticated' => [
        'title' => 'Nije autentificirano',
        'detail' => 'Potrebna je valjana vjerodajnica.',
    ],
    'forbidden' => [
        'title' => 'Zabranjeno',
        'detail' => 'Nemate dopuštenje za ovu radnju.',
    ],
    'not_found' => [
        'title' => 'Nije pronađeno',
        'detail' => 'Traženi endpoint ili resurs ne postoji.',
    ],
    'method_not_allowed' => [
        'title' => 'Metoda nije dopuštena',
        'detail' => 'Ovaj endpoint ne podržava tu HTTP metodu.',
    ],
    'too_many_requests' => [
        'title' => 'Previše zahtjeva',
        'detail' => 'Prekoračeno je ograničenje zahtjeva. Pokušajte kasnije.',
    ],
    'server_error' => [
        'title' => 'Greška poslužitelja',
        'detail' => 'Nešto je pošlo po krivu. Pokušajte kasnije.',
    ],
];
