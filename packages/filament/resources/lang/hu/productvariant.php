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
    'label' => 'Termékváltozat',
    'plural_label' => 'Termékváltozatok',
    'pages' => [
        'edit' => [
            'title' => 'Alapinformációk',
        ],
        'media' => [
            'title' => 'Média',
            'form' => [
                'no_selection' => [
                    'label' => 'Ehhez a termékváltozathoz jelenleg nincs kép kiválasztva.',
                ],
                'no_media_available' => [
                    'label' => 'Jelenleg nincs elérhető média ehhez a termékhez.',
                ],
                'images' => [
                    'label' => 'Elsődleges kép',
                    'helper_text' => 'Válaszd ki azt a termékképet, amely ezt a termékváltozatot képviseli.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Azonosítók',
        ],
        'inventory' => [
            'title' => 'Készlet',
        ],
        'shipping' => [
            'title' => 'Szállítás',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'Cikkszám (SKU)',
        ],
        'gtin' => [
            'label' => 'Globális kereskedelmi cikkszám (GTIN)',
        ],
        'mpn' => [
            'label' => 'Gyártói cikkszám (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'continue_selling' => [
            'label' => 'Értékesítés folytatása készlethiány esetén',
            'tooltip' => 'Bekapcsolva ez a változat a készlettől függetlenül rendelhető. Kikapcsolva a vásárlók csak az elérhető mennyiséget vehetik meg.',
        ],
        'sell_against_incoming' => [
            'label' => 'Értékesítés beérkező készletre',
            'tooltip' => 'A beérkező készletet elérhetőként számolja, így a vásárlók a várt egységeket is megrendelhetik.',
        ],
        'unit_quantity' => [
            'label' => 'Egység mennyiség',
            'tooltip' => 'Hány egyedi darabból áll egy egység.',
        ],
        'min_quantity' => [
            'label' => 'Minimális mennyiség',
            'tooltip' => 'A termékváltozat egy vásárlás során megvásárolható minimális mennyisége.',
        ],
        'quantity_increment' => [
            'label' => 'Mennyiség növelése',
            'tooltip' => 'A termékváltozat csak e mennyiség többszörösében vásárolható meg.',
        ],
        'tax_class_id' => [
            'label' => 'Adóosztály',
        ],
        'shippable' => [
            'label' => 'Szállítható',
        ],
        'fulfilment_methods' => [
            'label' => 'Teljesítési módok',
            'helper_text' => 'Hogyan teljesül ez a változat. Egy fizikai mód, például a szállítás vagy az átvétel bejelölése szállíthatóvá teszi. Ne jelöljön be semmit, ha nincs mit teljesíteni, például azonnali letöltésnél.',
            'unavailable' => 'A(z) :methods jelenleg nem érhető el, így az ügyfelek nem választhatják. Vegye ki a pipát, vagy tegye újra elérhetővé.',
        ],
        'length_value' => [
            'label' => 'Hossz',
        ],
        'length_unit' => [
            'label' => 'Hossz egység',
        ],
        'width_value' => [
            'label' => 'Szélesség',
        ],
        'width_unit' => [
            'label' => 'Szélesség egység',
        ],
        'height_value' => [
            'label' => 'Magasság',
        ],
        'height_unit' => [
            'label' => 'Magasság egység',
        ],
        'weight_value' => [
            'label' => 'Súly',
        ],
        'weight_unit' => [
            'label' => 'Súly egység',
        ],
    ],
];
