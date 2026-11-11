<?php

return [
    'shipping_discount' => [
        'name' => 'Precio de envío',
        'form' => [
            'methods' => [
                'label' => 'Métodos de envío',
                'add_label' => 'Añadir regla',
            ],
            'shipping_method_id' => [
                'label' => 'Método de envío',
                'placeholder' => 'Cualquier método de envío',
            ],
            'type' => [
                'label' => 'Tipo de descuento',
                'options' => [
                    'fixed' => 'Precio fijo',
                    'percentage' => 'Porcentaje de descuento',
                ],
            ],
            'percentage' => [
                'label' => 'Porcentaje de descuento (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Reglas',
            'rules_description' => 'Cada regla se aplica a un método de envío, o a todos los métodos cuando no se elige ninguno. Un método específico tiene prioridad sobre la regla general.',
            'add_rule' => 'Añadir regla',
            'remove_rule' => 'Eliminar regla',
            'no_rules' => 'Aún no hay reglas. Añade una para cambiar el coste del envío.',
            'method' => 'Método de envío',
            'method_any' => 'Cualquier método de envío',
            'type' => 'Efecto',
            'type_fixed' => 'Fijar el precio del envío',
            'type_percentage' => 'Aplicar un porcentaje de descuento',
            'percentage' => 'Porcentaje de descuento (%)',
            'prices' => 'El precio del envío pasa a ser',
            'prices_hint' => 'El cliente paga este importe por el envío. Deja una moneda en blanco para mantener su precio sin cambios; introduce 0 para envío gratuito.',
        ],
        'summary_free' => 'Envío gratuito',
        'summary_percentage' => ':percentage% de descuento en el envío',
        'summary_from' => 'Envío desde :amount',
    ],
];
