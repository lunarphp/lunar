<?php

return [
    'invalid_query' => [
        'title' => 'Interogare invalidă',
    ],
    'query' => [
        'malformed_parameter' => 'Parametrul :parameter este malformat.',
        'unknown_include' => 'Includere necunoscută ":value" pe :type. Permise: :allowed.',
        'include_too_deep' => 'Includerea ":value" depășește adâncimea maximă de :max.',
        'unknown_type' => 'Tip de resursă necunoscut ":value". Permise: :allowed.',
        'unknown_field' => 'Câmp necunoscut ":value" pe :type. Permise: :allowed.',
        'unknown_filter' => 'Filtru necunoscut ":value". Permise: :allowed.',
        'unknown_operator' => 'Operator necunoscut ":value" pentru filtrul ":filter". Permiși: :allowed.',
        'unknown_sort' => 'Sortare necunoscută ":value". Permise: :allowed.',
        'invalid_page_size' => 'page[size] trebuie să fie un număr întreg între 1 și :max.',
        'invalid_page_number' => 'page[number] trebuie să fie un număr întreg pozitiv.',
        'cursor_unsupported' => 'Resursa :type nu acceptă paginarea cu cursor.',
        'cursor_and_number' => 'page[cursor] și page[number] nu pot fi combinate.',
        'invalid_cursor' => 'page[cursor] nu este un cursor valid.',
        'unknown_page_key' => 'Cheie de paginare necunoscută ":value". Permise: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Negăsit',
        'detail' => 'Nu există nicio resursă :type cu id-ul ":id".',
    ],
    'invalid_header' => [
        'title' => 'Antet invalid',
        'detail' => 'Valoarea ":value" pentru antetul :header nu este recunoscută.',
    ],
    'invalid_cart_token' => [
        'title' => 'Token de coș invalid',
        'detail' => 'Tokenul X-Lunar-Cart este invalid sau a expirat.',
    ],
    'cart_not_found' => [
        'title' => 'Coș negăsit',
        'detail' => 'Coșul referit de X-Lunar-Cart nu mai există.',
    ],
    'customer_not_found' => [
        'title' => 'Fără client',
        'detail' => 'Utilizatorul autentificat nu are o înregistrare de client.',
    ],
    'validation_failed' => [
        'title' => 'Validarea a eșuat',
    ],
    'unauthenticated' => [
        'title' => 'Neautentificat',
        'detail' => 'Este necesară o credențială validă.',
    ],
    'forbidden' => [
        'title' => 'Interzis',
        'detail' => 'Nu aveți permisiunea de a efectua această acțiune.',
    ],
    'not_found' => [
        'title' => 'Negăsit',
        'detail' => 'Endpointul sau resursa solicitată nu există.',
    ],
    'method_not_allowed' => [
        'title' => 'Metodă nepermisă',
        'detail' => 'Acest endpoint nu acceptă această metodă HTTP.',
    ],
    'too_many_requests' => [
        'title' => 'Prea multe cereri',
        'detail' => 'Limita de cereri a fost depășită. Încercați din nou mai târziu.',
    ],
    'server_error' => [
        'title' => 'Eroare de server',
        'detail' => 'Ceva nu a funcționat. Încercați din nou mai târziu.',
    ],
];
