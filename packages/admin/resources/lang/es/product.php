<?php

return [

    'label' => 'Producto',

    'plural_label' => 'Productos',

    'tabs' => [
        'all' => 'Todo',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Actualmente en estado de borrador, este producto está oculto en todos los canales y grupos de clientes.',
        ],
        'availability' => [
            'customer_groups' => 'Este producto actualmente no está disponible para todos los grupos de clientes.',
            'channels' => 'Este producto actualmente no está disponible para todos los canales.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Estado',
            'states' => [
                'deleted' => 'Eliminado',
                'draft' => 'Borrador',
                'published' => 'Publicado',
            ],
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'brand' => [
            'label' => 'Marca',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Stock',
        ],
        'producttype' => [
            'label' => 'Tipo de Producto',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Actualizar Estado',
            'heading' => 'Actualizar Estado',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'brand' => [
            'label' => 'Marca',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Tipo de Producto',
        ],
        'status' => [
            'label' => 'Estado',
            'options' => [
                'published' => [
                    'label' => 'Publicado',
                    'description' => 'Este producto estará disponible en todos los grupos de clientes y canales habilitados',
                ],
                'draft' => [
                    'label' => 'Borrador',
                    'description' => 'Este producto estará oculto en todos los canales y grupos de clientes',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Etiquetas',
            'helper_text' => 'Separa las etiquetas presionando Enter, Tab o coma (,)',
        ],
        'collections' => [
            'label' => 'Colecciones',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Disponibilidad',
        ],
        'edit' => [
            'title' => 'Información Básica',
        ],
        'identifiers' => [
            'label' => 'Identificadores del Producto',
        ],
        'inventory' => [
            'label' => 'Inventario',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Clase de Impuesto',
                ],
                'tax_ref' => [
                    'label' => 'Referencia de Impuesto',
                    'helper_text' => 'Opcional, para integración con sistemas de terceros.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Envío',
        ],
        'variants' => [
            'label' => 'Variantes',
        ],
        'collections' => [
            'label' => 'Colecciones',
            'select_collection' => 'Seleccionar una colección',
        ],
        'associations' => [
            'label' => 'Asociaciones de Productos',
        ],
    ],

];
