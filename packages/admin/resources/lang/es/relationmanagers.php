<?php

return [
    'customer_groups' => [
        'title' => 'Grupos de Clientes',
        'actions' => [
            'attach' => [
                'label' => 'Adjuntar Grupo de Clientes',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nombre',
            ],
            'enabled' => [
                'label' => 'Habilitado',
            ],
            'starts_at' => [
                'label' => 'Fecha de Inicio',
            ],
            'ends_at' => [
                'label' => 'Fecha de Finalización',
            ],
            'visible' => [
                'label' => 'Visible',
            ],
            'purchasable' => [
                'label' => 'Comprable',
            ],
        ],
        'table' => [
            'description' => 'Asocia grupos de clientes a este :type para determinar su disponibilidad.',
            'name' => [
                'label' => 'Nombre',
            ],
            'enabled' => [
                'label' => 'Habilitado',
            ],
            'starts_at' => [
                'label' => 'Fecha de Inicio',
            ],
            'ends_at' => [
                'label' => 'Fecha de Finalización',
            ],
            'visible' => [
                'label' => 'Visible',
            ],
            'purchasable' => [
                'label' => 'Comprable',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Canales',
        'actions' => [
            'attach' => [
                'label' => 'Programar otro Canal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Habilitado',
                'helper_text_false' => 'Este canal no estará habilitado incluso si hay una fecha de inicio presente.',
            ],
            'starts_at' => [
                'label' => 'Fecha de Inicio',
                'helper_text' => 'Deja en blanco para estar disponible desde cualquier fecha.',
            ],
            'ends_at' => [
                'label' => 'Fecha de Finalización',
                'helper_text' => 'Deja en blanco para estar disponible indefinidamente.',
            ],
        ],
        'table' => [
            'description' => 'Determina qué canales están habilitados y programa la disponibilidad.',
            'name' => [
                'label' => 'Nombre',
            ],
            'enabled' => [
                'label' => 'Habilitado',
            ],
            'starts_at' => [
                'label' => 'Fecha de Inicio',
            ],
            'ends_at' => [
                'label' => 'Fecha de Finalización',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Medios',
        'title_plural' => 'Medios',
        'actions' => [
            'create' => [
                'label' => 'Crear Medio',
            ],
            'view' => [
                'label' => 'Ver',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nombre',
            ],
            'media' => [
                'label' => 'Imagen',
            ],
            'primary' => [
                'label' => 'Primario',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Imagen',
            ],
            'file' => [
                'label' => 'Archivo',
            ],
            'name' => [
                'label' => 'Nombre',
            ],
            'primary' => [
                'label' => 'Primario',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'Crear URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Idioma',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Predeterminado',
            ],
            'language' => [
                'label' => 'Idioma',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Predeterminado',
            ],
            'language' => [
                'label' => 'Idioma',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Precios de Grupo de Clientes',
        'title_plural' => 'Precios de Grupo de Clientes',
        'table' => [
            'heading' => 'Precios de Grupo de Clientes',
            'description' => 'Asocia precios a grupos de clientes para determinar el precio del producto.',
            'empty_state' => [
                'label' => 'No existen precios de grupos de clientes.',
                'description' => 'Crea un precio de grupo de clientes para comenzar.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Agregar Precio de Grupo de Clientes',
                    'modal' => [
                        'heading' => 'Crear Precio de Grupo de Clientes',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Precios',
        'title_plural' => 'Precios',
        'tab_name' => 'Descuentos por Cantidad',
        'table' => [
            'heading' => 'Descuentos por Cantidad',
            'description' => 'Reduce el precio cuando un cliente compra en mayores cantidades.',
            'empty_state' => [
                'label' => 'No existen descuentos por cantidad.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Agregar Descuento por Cantidad',
                ],
            ],
            'price' => [
                'label' => 'Precio',
            ],
            'customer_group' => [
                'label' => 'Grupo de Clientes',
                'placeholder' => 'Todos los Grupos de Clientes',
            ],
            'min_quantity' => [
                'label' => 'Cantidad Mínima',
            ],
            'currency' => [
                'label' => 'Moneda',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Precio',
                'helper_text' => 'El precio de compra, antes de descuentos.',
            ],
            'customer_group_id' => [
                'label' => 'Grupo de Clientes',
                'placeholder' => 'Todos los Grupos de Clientes',
                'helper_text' => 'Selecciona a qué grupo de clientes aplicar este precio.',
            ],
            'min_quantity' => [
                'label' => 'Cantidad Mínima',
                'helper_text' => 'Selecciona la cantidad mínima para la que estará disponible este precio.',
                'validation' => [
                    'unique' => 'El Grupo de Clientes y la Cantidad Mínima deben ser únicos.',
                ],
            ],
            'currency_id' => [
                'label' => 'Moneda',
                'helper_text' => 'Selecciona la moneda para este precio.',
            ],
            'compare_price' => [
                'label' => 'Precio Comparativo',
                'helper_text' => 'El precio original o RRP, para comparación con su precio de compra.',
            ],
            'basePrices' => [
                'title' => 'Precios',
                'form' => [
                    'price' => [
                        'label' => 'Precio',
                        'helper_text' => 'El precio de compra, antes de descuentos.',
                    ],
                    'compare_price' => [
                        'label' => 'Precio Comparativo',
                        'helper_text' => 'El precio original o RRP, para comparación con su precio de compra.',
                    ],
                ],
                'tooltip' => 'Generado automáticamente en base a las tasas de cambio de divisas.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Porcentaje',
            ],
            'tax_class' => [
                'label' => 'Clase Impositiva',
            ],
        ],
    ],
    'values' => [
        'title' => 'Valores',
        'form' => [
            'name' => [
                'label' => 'Nombre',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Nombre',
            ],
            'position' => [
                'label' => 'Posición',
            ],
        ],
    ],
];
