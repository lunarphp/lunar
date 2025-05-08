<?php

return [
    'label' => 'Zona de Envío',
    'label_plural' => 'Zonas de Envío',
    'form' => [
        'unrestricted' => [
            'content' => 'Esta zona de envío no tiene restricciones y estará disponible para todos los clientes al momento de pagar.',
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'type' => [
            'label' => 'Tipo',
            'options' => [
                'unrestricted' => 'Sin Restricciones',
                'countries' => 'Limitar a Países',
                'states' => 'Limitar a Estados / Provincias',
                'postcodes' => 'Limitar a Códigos Postales',
            ],
        ],
        'country' => [
            'label' => 'País',
        ],
        'states' => [
            'label' => 'Estados',
        ],
        'countries' => [
            'label' => 'Países',
        ],
        'postcodes' => [
            'label' => 'Códigos Postales',
            'helper' => 'Lista cada código postal en una nueva línea. Soporta comodines como NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'type' => [
            'label' => 'Tipo',
            'options' => [
                'unrestricted' => 'Sin Restricciones',
                'countries' => 'Limitar a Países',
                'states' => 'Limitar a Estados / Provincias',
                'postcodes' => 'Limitar a Códigos Postales',
            ],
        ],
    ],
];
