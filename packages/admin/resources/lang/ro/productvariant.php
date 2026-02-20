<?php

return [
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
        'stock' => [
            'label' => 'În stoc',
        ],
        'backorder' => [
            'label' => 'La precomandă',
        ],
        'purchasable' => [
            'label' => 'Disponibilitate la achiziție',
            'options' => [
                'always' => 'Întotdeauna',
                'in_stock' => 'În stoc',
                'in_stock_or_on_backorder' => 'În stoc sau la precomandă',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Cantitate unitate',
            'helper_text' => 'Câte articole individuale compun 1 unitate.',
        ],
        'min_quantity' => [
            'label' => 'Cantitate minimă',
            'helper_text' => 'Cantitatea minimă dintr-o variantă de produs care poate fi cumpărată într-o singură achiziție.',
        ],
        'quantity_increment' => [
            'label' => 'Increment cantitate',
            'helper_text' => 'Varianta de produs trebuie cumpărată în multipli ai acestei cantități.',
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
