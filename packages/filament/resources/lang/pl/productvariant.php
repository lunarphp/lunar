<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'Przychodzące',
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
        'continue_selling' => [
            'label' => 'Kontynuuj sprzedaż przy braku stanu',
            'tooltip' => 'Po włączeniu ten wariant można zamówić niezależnie od stanu magazynowego. Po wyłączeniu klienci mogą kupić tylko dostępne sztuki.',
        ],
        'sell_against_incoming' => [
            'label' => 'Sprzedawaj z przychodzącego stanu',
            'tooltip' => 'Przychodzący stan jest liczony jako dostępny, więc klienci mogą zamawiać oczekiwane sztuki.',
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
        'fulfilment_methods' => [
            'label' => 'Metody realizacji',
            'helper_text' => 'Jak realizowany jest ten wariant. Zaznaczenie metody fizycznej, takiej jak wysyłka lub odbiór osobisty, czyni go wysyłkowym. Nie zaznaczaj niczego, gdy nie ma czego realizować, np. przy natychmiastowym pobraniu.',
            'unavailable' => ':methods jest obecnie niedostępne, więc klienci nie mogą tego wybrać. Odznacz to lub ponownie udostępnij.',
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
