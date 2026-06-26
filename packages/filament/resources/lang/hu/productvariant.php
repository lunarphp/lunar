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
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'Utánrendelhető',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'Mindig',
                'in_stock' => 'Raktáron',
                'in_stock_or_on_backorder' => 'Raktáron vagy utánrendelhető',
            ],
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
