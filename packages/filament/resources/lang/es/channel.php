<?php

return [
    'label' => 'Canal',
    'plural_label' => 'Canales',
    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Predeterminado',
        ],
    ],

    'actions' => [
        'delete' => [
            'blocked' => 'No se pueden eliminar el canal predeterminado, los canales con historial de pedidos ni los canales que todavía usa una región o un carrito. Marca el canal como inactivo en su lugar.',
        ],
    ],

];
