<?php

return [
    'invalid_query' => [
        'title' => 'Ongeldige query',
    ],
    'query' => [
        'malformed_parameter' => 'De parameter :parameter is onjuist opgebouwd.',
        'unknown_include' => 'Onbekende include ":value" op :type. Toegestaan: :allowed.',
        'include_too_deep' => 'Include ":value" overschrijdt de maximale diepte van :max.',
        'unknown_type' => 'Onbekend resourcetype ":value". Toegestaan: :allowed.',
        'unknown_field' => 'Onbekend veld ":value" op :type. Toegestaan: :allowed.',
        'unknown_filter' => 'Onbekend filter ":value". Toegestaan: :allowed.',
        'unknown_operator' => 'Onbekende operator ":value" voor filter ":filter". Toegestaan: :allowed.',
        'unknown_sort' => 'Onbekende sortering ":value". Toegestaan: :allowed.',
        'invalid_page_size' => 'page[size] moet een geheel getal tussen 1 en :max zijn.',
        'invalid_page_number' => 'page[number] moet een positief geheel getal zijn.',
        'cursor_unsupported' => 'De resource :type ondersteunt geen cursorpaginering.',
        'cursor_and_number' => 'page[cursor] en page[number] kunnen niet worden gecombineerd.',
        'invalid_cursor' => 'page[cursor] is geen geldige cursor.',
        'unknown_page_key' => 'Onbekende pagineringssleutel ":value". Toegestaan: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Niet gevonden',
        'detail' => 'Er bestaat geen :type-resource met id ":id".',
    ],
    'invalid_header' => [
        'title' => 'Ongeldige header',
        'detail' => 'De waarde ":value" voor de header :header wordt niet herkend.',
    ],
    'invalid_cart_token' => [
        'title' => 'Ongeldig winkelwagentoken',
        'detail' => 'Het X-Lunar-Cart-token is ongeldig of verlopen.',
    ],
    'cart_not_found' => [
        'title' => 'Winkelwagen niet gevonden',
        'detail' => 'De winkelwagen waarnaar X-Lunar-Cart verwijst bestaat niet meer.',
    ],
    'customer_not_found' => [
        'title' => 'Geen klant',
        'detail' => 'De ingelogde gebruiker heeft geen klantrecord.',
    ],
    'validation_failed' => [
        'title' => 'Validatie mislukt',
    ],
    'unauthenticated' => [
        'title' => 'Niet geauthenticeerd',
        'detail' => 'Een geldig toegangsbewijs is vereist.',
    ],
    'forbidden' => [
        'title' => 'Verboden',
        'detail' => 'U heeft geen toestemming om deze actie uit te voeren.',
    ],
    'not_found' => [
        'title' => 'Niet gevonden',
        'detail' => 'Het gevraagde eindpunt of de resource bestaat niet.',
    ],
    'method_not_allowed' => [
        'title' => 'Methode niet toegestaan',
        'detail' => 'Dit eindpunt ondersteunt die HTTP-methode niet.',
    ],
    'too_many_requests' => [
        'title' => 'Te veel verzoeken',
        'detail' => 'De aanvraaglimiet is overschreden. Probeer het later opnieuw.',
    ],
    'server_error' => [
        'title' => 'Serverfout',
        'detail' => 'Er is iets misgegaan. Probeer het later opnieuw.',
    ],
];
