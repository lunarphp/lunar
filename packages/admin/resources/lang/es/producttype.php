<?php

return [

    'label' => 'Tipo de Producto',

    'plural_label' => 'Tipos de Producto',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'products_count' => [
            'label' => 'Cantidad de Productos',
        ],
        'product_attributes_count' => [
            'label' => 'Atributos de Producto',
        ],
        'variant_attributes_count' => [
            'label' => 'Atributos de Variante',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Atributos de Producto',
        ],
        'variant_attributes' => [
            'label' => 'Atributos de Variante',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
    ],

    'attributes' => [
        'no_groups' => 'No hay grupos de atributos disponibles.',
        'no_attributes' => 'No hay atributos disponibles.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este tipo de producto no puede ser eliminado ya que hay productos asociados.',
            ],
        ],
    ],

];
