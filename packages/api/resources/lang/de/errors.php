<?php

return [
    'invalid_query' => [
        'title' => 'Ungültige Abfrage',
    ],
    'query' => [
        'malformed_parameter' => 'Der Parameter :parameter ist fehlerhaft.',
        'unknown_include' => 'Unbekannter Include ":value" auf :type. Erlaubt: :allowed.',
        'include_too_deep' => 'Include ":value" überschreitet die maximale Tiefe von :max.',
        'unknown_type' => 'Unbekannter Ressourcentyp ":value". Erlaubt: :allowed.',
        'unknown_field' => 'Unbekanntes Feld ":value" auf :type. Erlaubt: :allowed.',
        'unknown_filter' => 'Unbekannter Filter ":value". Erlaubt: :allowed.',
        'unknown_operator' => 'Unbekannter Operator ":value" für Filter ":filter". Erlaubt: :allowed.',
        'unknown_sort' => 'Unbekannte Sortierung ":value". Erlaubt: :allowed.',
        'invalid_page_size' => 'page[size] muss eine ganze Zahl zwischen 1 und :max sein.',
        'invalid_page_number' => 'page[number] muss eine positive ganze Zahl sein.',
        'cursor_unsupported' => 'Die Ressource :type unterstützt keine Cursor-Paginierung.',
        'cursor_and_number' => 'page[cursor] und page[number] können nicht kombiniert werden.',
        'invalid_cursor' => 'page[cursor] ist kein gültiger Cursor.',
        'unknown_page_key' => 'Unbekannter Paginierungsschlüssel ":value". Erlaubt: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Nicht gefunden',
        'detail' => 'Es existiert keine Ressource :type mit der ID ":id".',
    ],
    'invalid_header' => [
        'title' => 'Ungültiger Header',
        'detail' => 'Der Wert ":value" für den Header :header wird nicht erkannt.',
    ],
    'invalid_cart_token' => [
        'title' => 'Ungültiges Warenkorb-Token',
        'detail' => 'Das X-Lunar-Cart-Token ist ungültig oder abgelaufen.',
    ],
    'cart_not_found' => [
        'title' => 'Warenkorb nicht gefunden',
        'detail' => 'Der von X-Lunar-Cart referenzierte Warenkorb existiert nicht mehr.',
    ],
    'customer_not_found' => [
        'title' => 'Kein Kunde',
        'detail' => 'Der angemeldete Benutzer hat keinen Kundendatensatz.',
    ],
    'validation_failed' => [
        'title' => 'Validierung fehlgeschlagen',
    ],
    'unauthenticated' => [
        'title' => 'Nicht authentifiziert',
        'detail' => 'Ein gültiger Zugangsnachweis ist erforderlich.',
    ],
    'forbidden' => [
        'title' => 'Verboten',
        'detail' => 'Sie haben keine Berechtigung für diese Aktion.',
    ],
    'not_found' => [
        'title' => 'Nicht gefunden',
        'detail' => 'Der angeforderte Endpunkt oder die Ressource existiert nicht.',
    ],
    'method_not_allowed' => [
        'title' => 'Methode nicht erlaubt',
        'detail' => 'Dieser Endpunkt unterstützt diese HTTP-Methode nicht.',
    ],
    'too_many_requests' => [
        'title' => 'Zu viele Anfragen',
        'detail' => 'Das Anfragelimit wurde überschritten. Bitte später erneut versuchen.',
    ],
    'server_error' => [
        'title' => 'Serverfehler',
        'detail' => 'Etwas ist schiefgelaufen. Bitte später erneut versuchen.',
    ],
];
