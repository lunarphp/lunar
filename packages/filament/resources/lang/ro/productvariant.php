<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'În curs de sosire',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
    'label' => 'Variantă produs',
    'plural_label' => 'Variante de produs',
    'pages' => [
        'edit' => [
            'title' => 'Informații de bază',
        ],
        'media' => [
            'title' => 'Media',
            'form' => [
                'no_selection' => [
                    'label' => 'Nu aveți selectată nicio imagine pentru această variantă.',
                ],
                'no_media_available' => [
                    'label' => 'Momentan nu există media disponibilă pentru acest produs.',
                ],
                'images' => [
                    'label' => 'Imagine principală',
                    'helper_text' => 'Selectați imaginea produsului care reprezintă această variantă.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identificatori',
        ],
        'inventory' => [
            'title' => 'Stoc',
        ],
        'shipping' => [
            'title' => 'Livrare',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'Cod stoc intern (SKU)',
        ],
        'gtin' => [
            'label' => 'Număr global de articol comercial (GTIN)',
        ],
        'mpn' => [
            'label' => 'Număr de parte al producătorului (MPN)',
        ],
        'ean' => [
            'label' => 'Cod de bare (UPC/EAN)',
        ],
        'continue_selling' => [
            'label' => 'Continuă vânzarea când stocul este epuizat',
            'tooltip' => 'Când este activat, această variantă poate fi comandată indiferent de stoc. Când este dezactivat, clienții pot cumpăra doar ce este disponibil.',
        ],
        'sell_against_incoming' => [
            'label' => 'Vinde din stocul în curs de sosire',
            'tooltip' => 'Stocul în curs de sosire este considerat disponibil, astfel încât clienții pot comanda unitățile așteptate.',
        ],
        'unit_quantity' => [
            'label' => 'Cantitate unitate',
            'tooltip' => 'Câte articole individuale compun 1 unitate.',
        ],
        'min_quantity' => [
            'label' => 'Cantitate minimă',
            'tooltip' => 'Cantitatea minimă dintr-o variantă de produs care poate fi cumpărată într-o singură achiziție.',
        ],
        'quantity_increment' => [
            'label' => 'Increment cantitate',
            'tooltip' => 'Varianta de produs trebuie cumpărată în multipli ai acestei cantități.',
        ],
        'tax_class_id' => [
            'label' => 'Clasă de taxe',
        ],
        'shippable' => [
            'label' => 'Expediabil',
        ],
        'fulfilment_methods' => [
            'label' => 'Metode de onorare',
            'helper_text' => 'Cum este onorată această variantă. Bifarea unei metode fizice, cum ar fi expedierea sau ridicarea, o face expediabilă. Nu bifa nimic când nu este nimic de onorat, de exemplu o descărcare imediată.',
            'unavailable' => ':methods nu este disponibil momentan, așa că clienții nu îl pot alege. Debifează-l sau fă-l din nou disponibil.',
        ],
        'length_value' => [
            'label' => 'Lungime',
        ],
        'length_unit' => [
            'label' => 'Unitate lungime',
        ],
        'width_value' => [
            'label' => 'Lățime',
        ],
        'width_unit' => [
            'label' => 'Unitate lățime',
        ],
        'height_value' => [
            'label' => 'Înălțime',
        ],
        'height_unit' => [
            'label' => 'Unitate înălțime',
        ],
        'weight_value' => [
            'label' => 'Greutate',
        ],
        'weight_unit' => [
            'label' => 'Unitate greutate',
        ],
    ],
];
