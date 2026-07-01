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
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'Na zamówienie',
        ],
        'selling_policy' => [
            'label' => 'Zasady sprzedaży',
            'tooltip' => 'Kiedy ten wariant można kupić. „Tylko gdy jest w magazynie” sprzedaje tylko, gdy są dostępne sztuki; „W magazynie lub na zamówienie” sprzedaje także limit zamówień oczekujących; „Zawsze” całkowicie ignoruje stan magazynowy.',
            'options' => [
                'always' => 'Zawsze',
                'in_stock' => 'Tylko gdy jest w magazynie',
                'in_stock_or_on_backorder' => 'W magazynie lub na zamówienie',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Ilość jednostkowa',
            'tooltip' => 'Ile pojedynczych przedmiotów stanowi 1 jednostkę.',
        ],
        'min_quantity' => [
            'label' => 'Minimalna ilość',
            'tooltip' => 'Minimalna ilość, którą klient musi zakupić.',
        ],
        'quantity_increment' => [
            'label' => 'Zwiększenie ilości',
            'tooltip' => 'Wariant produktu musi być kupowany w wielokrotnościach tej ilości.',
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
