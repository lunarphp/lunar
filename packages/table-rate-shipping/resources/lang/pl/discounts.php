<?php

return [
    'shipping_discount' => [
        'name' => 'Cena dostawy',
        'form' => [
            'methods' => [
                'label' => 'Metody dostawy',
                'add_label' => 'Dodaj regułę',
            ],
            'shipping_method_id' => [
                'label' => 'Metoda dostawy',
                'placeholder' => 'Dowolna metoda dostawy',
            ],
            'type' => [
                'label' => 'Typ rabatu',
                'options' => [
                    'fixed' => 'Stała cena',
                    'percentage' => 'Rabat procentowy',
                ],
            ],
            'percentage' => [
                'label' => 'Rabat procentowy (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Reguły',
            'rules_description' => 'Każda reguła dotyczy jednej metody dostawy lub wszystkich metod, gdy żadna nie została wybrana. Konkretna metoda ma pierwszeństwo przed regułą ogólną.',
            'add_rule' => 'Dodaj regułę',
            'remove_rule' => 'Usuń regułę',
            'no_rules' => 'Brak reguł. Dodaj regułę, aby zmienić koszt dostawy.',
            'method' => 'Metoda dostawy',
            'method_any' => 'Dowolna metoda dostawy',
            'type' => 'Efekt',
            'type_fixed' => 'Ustaw cenę dostawy',
            'type_percentage' => 'Zastosuj rabat procentowy',
            'percentage' => 'Rabat procentowy (%)',
            'prices' => 'Cena dostawy wynosi',
            'prices_hint' => 'Klient płaci tę kwotę za dostawę. Pozostaw walutę pustą, aby nie zmieniać jej ceny; wpisz 0, aby dostawa była bezpłatna.',
        ],
        'summary_free' => 'Bezpłatna dostawa',
        'summary_percentage' => ':percentage% rabatu na dostawę',
        'summary_from' => 'Dostawa od :amount',
    ],
];
