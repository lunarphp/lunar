<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'Dolazno',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
    'label' => 'Varijanta proizvoda',
    'plural_label' => 'Varijante proizvoda',
    'pages' => [
        'edit' => [
            'title' => 'Osnovne informacije',
        ],
        'media' => [
            'title' => 'Mediji',
            'form' => [
                'no_selection' => [
                    'label' => 'Trenutno nije odabrana nijedna slika za ovu varijantu.',
                ],
                'no_media_available' => [
                    'label' => 'Trenutno nema dostupnih medija za ovaj proizvod.',
                ],
                'images' => [
                    'label' => 'Glavna slika',
                    'helper_text' => 'Odaberite sliku proizvoda koja predstavlja ovu varijantu.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identifikatori',
        ],
        'inventory' => [
            'title' => 'Zaliha',
        ],
        'shipping' => [
            'title' => 'Dostava',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Globalni broj trgovinske jedinice (GTIN)',
        ],
        'mpn' => [
            'label' => 'Kataloški broj proizvođača (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'continue_selling' => [
            'label' => 'Nastavi prodaju kad nema zalihe',
            'tooltip' => 'Kad je uključeno, ova se varijanta može naručiti bez obzira na zalihu. Kad je isključeno, kupci mogu kupiti samo dostupno.',
        ],
        'sell_against_incoming' => [
            'label' => 'Prodaja na temelju dolazne zalihe',
            'tooltip' => 'Dolazna zaliha računa se kao dostupna, pa kupci mogu naručiti jedinice koje očekujete.',
        ],
        'unit_quantity' => [
            'label' => 'Broj komada',
            'tooltip' => 'Koliko pojedinačnih artikala čini 1 jedinicu.',
        ],
        'min_quantity' => [
            'label' => 'Najmanja količina',
            'tooltip' => 'Najmanja količina varijante proizvoda koja se može kupiti u jednoj kupnji.',
        ],
        'quantity_increment' => [
            'label' => 'Korak količine',
            'tooltip' => 'Varijanta proizvoda mora se kupovati u višekratnicima ove količine.',
        ],
        'tax_class_id' => [
            'label' => 'Porezni razred',
        ],
        'shippable' => [
            'label' => 'Moguća dostava',
        ],
        'fulfilment_methods' => [
            'label' => 'Načini ispunjenja',
            'helper_text' => 'Kako se ova varijanta ispunjava. Označavanje fizičkog načina, poput slanja ili preuzimanja, čini je dostavljivom. Ne označite ništa kada se nema što ispuniti, npr. za trenutno preuzimanje datoteke.',
            'unavailable' => ':methods trenutno nije dostupno pa ga kupci ne mogu odabrati. Odznačite ga ili ga ponovno učinite dostupnim.',
        ],
        'length_value' => [
            'label' => 'Duljina',
        ],
        'length_unit' => [
            'label' => 'Jedinica duljine',
        ],
        'width_value' => [
            'label' => 'Širina',
        ],
        'width_unit' => [
            'label' => 'Jedinica širine',
        ],
        'height_value' => [
            'label' => 'Visina',
        ],
        'height_unit' => [
            'label' => 'Jedinica visine',
        ],
        'weight_value' => [
            'label' => 'Težina',
        ],
        'weight_unit' => [
            'label' => 'Jedinica težine',
        ],
    ],
];
