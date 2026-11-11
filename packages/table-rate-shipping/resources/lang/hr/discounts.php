<?php

return [
    'shipping_discount' => [
        'name' => 'Cijena dostave',
        'form' => [
            'methods' => [
                'label' => 'Načini dostave',
                'add_label' => 'Dodaj pravilo',
            ],
            'shipping_method_id' => [
                'label' => 'Način dostave',
                'placeholder' => 'Bilo koji način dostave',
            ],
            'type' => [
                'label' => 'Vrsta popusta',
                'options' => [
                    'fixed' => 'Fiksna cijena',
                    'percentage' => 'Postotak popusta',
                ],
            ],
            'percentage' => [
                'label' => 'Postotak popusta (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Pravila',
            'rules_description' => 'Svako pravilo primjenjuje se na jedan način dostave ili na sve načine ako nijedan nije odabran. Određeni način ima prednost pred općim pravilom.',
            'add_rule' => 'Dodaj pravilo',
            'remove_rule' => 'Ukloni pravilo',
            'no_rules' => 'Još nema pravila. Dodajte jedno da promijenite cijenu dostave.',
            'method' => 'Način dostave',
            'method_any' => 'Bilo koji način dostave',
            'type' => 'Učinak',
            'type_fixed' => 'Postavi cijenu dostave',
            'type_percentage' => 'Primijeni postotni popust',
            'percentage' => 'Postotak popusta (%)',
            'prices' => 'Cijena dostave postaje',
            'prices_hint' => 'Kupac plaća ovaj iznos za dostavu. Ostavite valutu praznom da njezina cijena ostane nepromijenjena; unesite 0 za besplatnu dostavu.',
        ],
        'summary_free' => 'Besplatna dostava',
        'summary_percentage' => ':percentage% popusta na dostavu',
        'summary_from' => 'Dostava od :amount',
    ],
];
