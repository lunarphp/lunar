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
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'La precomandă',
        ],
        'selling_policy' => [
            'label' => 'Politică de vânzare',
            'tooltip' => 'Când poate fi cumpărată această variantă. „În stoc” vinde doar cât timp există unități disponibile; „În stoc sau la precomandă” vinde și limita de precomandă; „Întotdeauna” ignoră complet stocul.',
            'options' => [
                'always' => 'Întotdeauna',
                'in_stock' => 'În stoc',
                'in_stock_or_on_backorder' => 'În stoc sau la precomandă',
            ],
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
