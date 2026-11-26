<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'Entrante',
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
        'continue_selling' => [
            'label' => 'Seguir vendiendo sin stock',
            'tooltip' => 'Si está activado, esta variante se puede pedir sin importar el stock. Si está desactivado, los clientes solo pueden comprar lo disponible.',
        ],
        'sell_against_incoming' => [
            'label' => 'Vender contra stock entrante',
            'tooltip' => 'Cuenta el stock entrante como disponible, para que los clientes puedan pedir unidades que esperas recibir.',
        ],
        'unit_quantity' => [
            'label' => 'Cantidad por Unidad',
            'tooltip' => 'Cuántos artículos individuales componen 1 unidad.',
        ],
        'min_quantity' => [
            'label' => 'Cantidad Mínima',
            'tooltip' => 'La cantidad mínima de una variante de producto que se puede comprar en una sola compra.',
        ],
        'quantity_increment' => [
            'label' => 'Incremento de Cantidad',
            'tooltip' => 'La variante de producto debe comprarse en múltiplos de esta cantidad.',
        ],
        'tax_class_id' => [
            'label' => 'Clase Impositiva',
        ],
        'shippable' => [
            'label' => 'Enviable',
        ],
        'fulfilment_methods' => [
            'label' => 'Métodos de cumplimiento',
            'helper_text' => 'Cómo se cumple esta variante. Marcar un método físico, como envío o recogida, la hace enviable. No marques ninguno cuando no haya nada que cumplir, como una descarga inmediata.',
            'unavailable' => ':methods no está disponible ahora mismo, así que los clientes no pueden elegirlo. Desmárcalo o vuelve a hacerlo disponible.',
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
