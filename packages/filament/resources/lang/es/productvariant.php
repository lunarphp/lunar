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
    'label' => 'Variante de Producto',
    'plural_label' => 'Variantes de Producto',
    'pages' => [
        'edit' => [
            'title' => 'Información Básica',
        ],
        'media' => [
            'title' => 'Medios',
            'form' => [
                'no_selection' => [
                    'label' => 'Actualmente no tienes una imagen seleccionada para esta variante.',
                ],
                'no_media_available' => [
                    'label' => 'Actualmente no hay medios disponibles para este producto.',
                ],
                'images' => [
                    'label' => 'Imagen Principal',
                    'helper_text' => 'Selecciona la imagen del producto que representa esta variante.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identificadores',
        ],
        'inventory' => [
            'title' => 'Inventario',
        ],
        'shipping' => [
            'title' => 'Envío',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Número Global de Artículo Comercial (GTIN)',
        ],
        'mpn' => [
            'label' => 'Número de Parte del Fabricante (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'tooltip' => 'Units on hand at your default location. Changing this records a stock adjustment.',
            'label' => 'En Stock',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'En Pedido Pendiente',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'Siempre',
                'in_stock' => 'En Stock',
                'in_stock_or_on_backorder' => 'En Stock o en Pedido Pendiente',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Cantidad por Unidad',
            'helper_text' => 'Cuántos artículos individuales componen 1 unidad.',
        ],
        'min_quantity' => [
            'label' => 'Cantidad Mínima',
            'helper_text' => 'La cantidad mínima de una variante de producto que se puede comprar en una sola compra.',
        ],
        'quantity_increment' => [
            'label' => 'Incremento de Cantidad',
            'helper_text' => 'La variante de producto debe comprarse en múltiplos de esta cantidad.',
        ],
        'tax_class_id' => [
            'label' => 'Clase Impositiva',
        ],
        'shippable' => [
            'label' => 'Enviable',
        ],
        'length_value' => [
            'label' => 'Longitud',
        ],
        'length_unit' => [
            'label' => 'Unidad de Longitud',
        ],
        'width_value' => [
            'label' => 'Anchura',
        ],
        'width_unit' => [
            'label' => 'Unidad de Anchura',
        ],
        'height_value' => [
            'label' => 'Altura',
        ],
        'height_unit' => [
            'label' => 'Unidad de Altura',
        ],
        'weight_value' => [
            'label' => 'Peso',
        ],
        'weight_unit' => [
            'label' => 'Unidad de Peso',
        ],
    ],
];
