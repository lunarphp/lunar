<?php

return [
    'selection' => [
        'malformed' => 'De bundelselectie is ongeldig opgebouwd.',
        'unknown_group' => 'De bundel heeft geen optiegroep ":id".',
        'unknown_component' => 'Optie ":id" is niet beschikbaar in ":group".',
        'duplicate_component' => 'Een optie in ":group" is meer dan één keer gekozen.',
        'count' => 'Kies tussen :min en :max opties in ":group".',
        'unavailable' => 'Bundelonderdeel ":identifier" is niet beschikbaar in dit aantal.',
    ],
    'nesting' => [
        'is_component' => 'Een variant die deel uitmaakt van een andere bundel kan zelf geen bundel zijn.',
        'is_bundle' => 'Een bundel kan geen andere bundel bevatten.',
        'self' => 'Een bundel kan zijn eigen variant niet bevatten.',
    ],
    'definition' => [
        'empty' => 'Een bundel heeft minstens één onderdeel nodig.',
        'too_many_components' => 'Een bundel mag niet meer dan :max onderdelen hebben.',
        'unknown_group' => 'De optiegroep hoort niet bij deze bundel.',
        'group_selections' => 'De selectielimieten van ":group" moeten voldoen aan minimum <= maximum <= aantal opties.',
    ],
    'pricing' => [
        'missing_component_price' => 'Onderdeel ":identifier" heeft geen prijs in :currency, dus de bundel kan in die valuta niet geprijsd worden.',
    ],
    'console' => [
        'repriced' => ':count bundels opnieuw geprijsd.',
    ],
];
