<?php

return [
    'invalid_query' => [
        'title' => 'Érvénytelen lekérdezés',
    ],
    'query' => [
        'malformed_parameter' => 'A(z) :parameter paraméter hibás formátumú.',
        'unknown_include' => 'Ismeretlen beágyazás ":value" a(z) :type erőforráson. Engedélyezett: :allowed.',
        'include_too_deep' => 'A(z) ":value" beágyazás meghaladja a maximális :max mélységet.',
        'unknown_type' => 'Ismeretlen erőforrástípus ":value". Engedélyezett: :allowed.',
        'unknown_field' => 'Ismeretlen mező ":value" a(z) :type erőforráson. Engedélyezett: :allowed.',
        'unknown_filter' => 'Ismeretlen szűrő ":value". Engedélyezett: :allowed.',
        'unknown_operator' => 'Ismeretlen operátor ":value" a(z) ":filter" szűrőhöz. Engedélyezett: :allowed.',
        'unknown_sort' => 'Ismeretlen rendezés ":value". Engedélyezett: :allowed.',
        'invalid_page_size' => 'A page[size] értéke 1 és :max közötti egész szám legyen.',
        'invalid_page_number' => 'A page[number] értéke pozitív egész szám legyen.',
        'cursor_unsupported' => 'A(z) :type erőforrás nem támogatja a kurzoros lapozást.',
        'cursor_and_number' => 'A page[cursor] és a page[number] nem használható együtt.',
        'invalid_cursor' => 'A page[cursor] nem érvényes kurzor.',
        'unknown_page_key' => 'Ismeretlen lapozási kulcs ":value". Engedélyezett: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Nem található',
        'detail' => 'Nem létezik ":id" azonosítójú :type erőforrás.',
    ],
    'invalid_header' => [
        'title' => 'Érvénytelen fejléc',
        'detail' => 'A(z) :header fejléc ":value" értéke nem ismert.',
    ],
    'invalid_cart_token' => [
        'title' => 'Érvénytelen kosár token',
        'detail' => 'Az X-Lunar-Cart token érvénytelen vagy lejárt.',
    ],
    'cart_not_found' => [
        'title' => 'A kosár nem található',
        'detail' => 'Az X-Lunar-Cart által hivatkozott kosár már nem létezik.',
    ],
    'customer_not_found' => [
        'title' => 'Nincs vásárló',
        'detail' => 'A hitelesített felhasználóhoz nem tartozik vásárlói rekord.',
    ],
    'validation_failed' => [
        'title' => 'A validálás sikertelen',
    ],
    'unauthenticated' => [
        'title' => 'Nincs hitelesítve',
        'detail' => 'Érvényes hitelesítő adat szükséges.',
    ],
    'forbidden' => [
        'title' => 'Tiltott',
        'detail' => 'Nincs jogosultsága a művelet végrehajtásához.',
    ],
    'not_found' => [
        'title' => 'Nem található',
        'detail' => 'A kért végpont vagy erőforrás nem létezik.',
    ],
    'method_not_allowed' => [
        'title' => 'A metódus nem engedélyezett',
        'detail' => 'Ez a végpont nem támogatja ezt a HTTP metódust.',
    ],
    'too_many_requests' => [
        'title' => 'Túl sok kérés',
        'detail' => 'A kérési korlát túllépve. Próbálja újra később.',
    ],
    'server_error' => [
        'title' => 'Szerverhiba',
        'detail' => 'Hiba történt. Próbálja újra később.',
    ],
];
