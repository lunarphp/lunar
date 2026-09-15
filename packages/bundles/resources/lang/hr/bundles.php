<?php

return [
    'selection' => [
        'malformed' => 'Odabir paketa nije ispravno oblikovan.',
        'unknown_group' => 'Paket nema grupu opcija ":id".',
        'unknown_component' => 'Opcija ":id" nije dostupna u ":group".',
        'duplicate_component' => 'Opcija u ":group" odabrana je više puta.',
        'count' => 'Odaberite između :min i :max opcija u ":group".',
        'unavailable' => 'Komponenta paketa ":identifier" nije dostupna u ovoj količini.',
    ],
    'nesting' => [
        'is_component' => 'Varijanta koja je dio drugog paketa ne može sama biti paket.',
        'is_bundle' => 'Paket ne može sadržavati drugi paket.',
        'self' => 'Paket ne može sadržavati vlastitu varijantu.',
    ],
    'definition' => [
        'empty' => 'Paket treba barem jednu komponentu.',
        'too_many_components' => 'Paket ne može imati više od :max komponenti.',
        'unknown_group' => 'Grupa opcija ne pripada ovom paketu.',
        'group_selections' => 'Ograničenja odabira za ":group" moraju zadovoljiti minimum <= maksimum <= broj opcija.',
    ],
    'pricing' => [
        'missing_component_price' => 'Komponenta ":identifier" nema cijenu u :currency, pa se paket ne može odrediti u toj valuti.',
    ],
    'console' => [
        'repriced' => 'Ponovno izračunate cijene za :count paketa.',
    ],
];
