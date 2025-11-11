<?php

return [
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
        'stock' => [
            'label' => 'Raktáron',
        ],
        'backorder' => [
            'label' => 'Utánrendelhető',
        ],
        'purchasable' => [
            'label' => 'Vásárolhatóság',
            'options' => [
                'always' => 'Mindig',
                'in_stock' => 'Raktáron',
                'in_stock_or_on_backorder' => 'Raktáron vagy utánrendelhető',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Egység mennyiség',
            'helper_text' => 'Hány egyedi darabból áll egy egység.',
        ],
        'min_quantity' => [
            'label' => 'Minimális mennyiség',
            'helper_text' => 'A termékváltozat egy vásárlás során megvásárolható minimális mennyisége.',
        ],
        'quantity_increment' => [
            'label' => 'Mennyiség növelése',
            'helper_text' => 'A termékváltozat csak e mennyiség többszörösében vásárolható meg.',
        ],
        'tax_class_id' => [
            'label' => 'Adóosztály',
        ],
        'shippable' => [
            'label' => 'Szállítható',
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
