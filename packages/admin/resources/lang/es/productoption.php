<?php

return [

    'label' => 'Opción de Producto',

    'plural_label' => 'Opciones de Producto',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'label' => [
            'label' => 'Etiqueta',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'shared' => [
            'label' => 'Compartido',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'label' => [
            'label' => 'Etiqueta',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Variantes de Producto Guardadas',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Cancelar',
                ],
                'save-options' => [
                    'label' => 'Guardar Opciones',
                ],
                'add-shared-option' => [
                    'label' => 'Agregar Opción Compartida',
                    'form' => [
                        'product_option' => [
                            'label' => 'Opción de Producto',
                        ],
                        'no_shared_components' => [
                            'label' => 'No hay opciones compartidas disponibles.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Agregar Opción',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'No hay opciones de producto configuradas',
                    'description' => 'Agrega una opción de producto compartida o restringida para comenzar a generar algunas variantes.',
                ],
            ],
            'options-table' => [
                'title' => 'Opciones de Producto',
                'configure-options' => [
                    'label' => 'Configurar Opciones',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Opción',
                    ],
                    'values' => [
                        'label' => 'Valores',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Variantes de Producto',
                'actions' => [
                    'create' => [
                        'label' => 'Crear Variante',
                    ],
                    'edit' => [
                        'label' => 'Editar',
                    ],
                    'delete' => [
                        'label' => 'Eliminar',
                    ],
                ],
                'empty' => [
                    'heading' => 'No Hay Variantes Configuradas',
                ],
                'table' => [
                    'new' => [
                        'label' => 'NUEVO',
                    ],
                    'option' => [
                        'label' => 'Opción',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Precio',
                    ],
                    'stock' => [
                        'label' => 'Stock',
                    ],
                ],
            ],
        ],
    ],

];
