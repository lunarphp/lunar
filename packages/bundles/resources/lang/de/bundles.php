<?php

return [
    'selection' => [
        'malformed' => 'Die Bundle-Auswahl ist fehlerhaft.',
        'unknown_group' => 'Das Bundle hat keine Optionsgruppe ":id".',
        'unknown_component' => 'Option ":id" ist in ":group" nicht verfügbar.',
        'duplicate_component' => 'Eine Option in ":group" wurde mehrfach gewählt.',
        'count' => 'Wählen Sie zwischen :min und :max Optionen in ":group".',
        'unavailable' => 'Bundle-Komponente ":identifier" ist in dieser Menge nicht verfügbar.',
    ],
    'nesting' => [
        'is_component' => 'Eine Variante, die Teil eines anderen Bundles ist, kann selbst kein Bundle sein.',
        'is_bundle' => 'Ein Bundle kann kein anderes Bundle enthalten.',
        'self' => 'Ein Bundle kann nicht seine eigene Variante enthalten.',
    ],
    'definition' => [
        'empty' => 'Ein Bundle benötigt mindestens eine Komponente.',
        'too_many_components' => 'Ein Bundle darf nicht mehr als :max Komponenten haben.',
        'unknown_group' => 'Die Optionsgruppe gehört nicht zu diesem Bundle.',
        'group_selections' => 'Die Auswahlgrenzen für ":group" müssen Minimum <= Maximum <= Anzahl der Optionen erfüllen.',
    ],
    'pricing' => [
        'missing_component_price' => 'Komponente ":identifier" hat keinen Preis in :currency, daher kann das Bundle in dieser Währung nicht bepreist werden.',
    ],
    'console' => [
        'repriced' => ':count Bundles neu bepreist.',
    ],
];
