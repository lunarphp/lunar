<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
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
        'stock' => [
            'tooltip' => 'Units on hand at your default location. Changing this records a stock adjustment.',
            'label' => 'Na zalihi',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'Naručeno unaprijed',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'Uvijek',
                'in_stock' => 'Na zalihi',
                'in_stock_or_on_backorder' => 'Na zalihi ili naručeno unaprijed',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Broj komada',
            'helper_text' => 'Koliko pojedinačnih artikala čini 1 jedinicu.',
        ],
        'min_quantity' => [
            'label' => 'Najmanja količina',
            'helper_text' => 'Najmanja količina varijante proizvoda koja se može kupiti u jednoj kupnji.',
        ],
        'quantity_increment' => [
            'label' => 'Korak količine',
            'helper_text' => 'Varijanta proizvoda mora se kupovati u višekratnicima ove količine.',
        ],
        'tax_class_id' => [
            'label' => 'Porezni razred',
        ],
        'shippable' => [
            'label' => 'Moguća dostava',
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
