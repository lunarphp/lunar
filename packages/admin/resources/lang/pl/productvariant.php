<?php

return [
    'label' => 'Wariant produktu',
    'plural_label' => 'Warianty produktów',
    'pages' => [
        'edit' => [
            'title' => 'Podstawowe informacje',
        ],
        'media' => [
            'title' => 'Media',
            'form' => [
                'no_selection' => [
                    'label' => 'Nie masz obecnie wybranych obrazów dla tego wariantu.',
                ],
                'no_media_available' => [
                    'label' => 'Brak dostępnych mediów.',
                ],
                'images' => [
                    'label' => 'Obraz główny',
                    'helper_text' => 'Obraz ten będzie wyświetlany jako główny obraz wariantu produktu.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identyfikatory',
        ],
        'inventory' => [
            'title' => 'Inwentarz',
        ],
        'shipping' => [
            'title' => 'Wysyłka',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Global Trade Item Number (GTIN)',
        ],
        'mpn' => [
            'label' => 'Manufacturer Part Number (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'label' => 'Dostępny',
        ],
        'backorder' => [
            'label' => 'Na zamówienie',
        ],
        'purchasable' => [
            'label' => 'Możliwość zakupu',
            'options' => [
                'always' => 'Zawsze',
                'in_stock' => 'Tylko gdy jest w magazynie',
                'backorder' => 'Na zamówienie',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Ilość jednostkowa',
            'helper_text' => 'Ile pojedynczych przedmiotów stanowi 1 jednostkę.',
        ],
        'min_quantity' => [
            'label' => 'Minimalna ilość',
            'helper_text' => 'Minimalna ilość, którą klient musi zakupić.',
        ],
        'quantity_increment' => [
            'label' => 'Zwiększenie ilości',
            'helper_text' => 'Wariant produktu musi być kupowany w wielokrotnościach tej ilości.',
        ],
        'tax_class_id' => [
            'label' => 'Klasa podatkowa',
        ],
        'shippable' => [
            'label' => 'Możliwość wysyłki',
        ],
        'length_value' => [
            'label' => 'Długość',
        ],
        'length_unit' => [
            'label' => 'Jednostka długości',
        ],
        'width_value' => [
            'label' => 'Szerokość',
        ],
        'width_unit' => [
            'label' => 'Jednostka szerokości',
        ],
        'height_value' => [
            'label' => 'Wysokość',
        ],
        'height_unit' => [
            'label' => 'Jednostka wysokości',
        ],
        'weight_value' => [
            'label' => 'Waga',
        ],
        'weight_unit' => [
            'label' => 'Jednostka wagi',
        ],
    ],
];
