<?php

return [
    'label' => 'Zona de envio',
    'label_plural' => 'Zonas de envio',
    'form' => [
        'unrestricted' => [
            'content' => 'Esta zona de envio não possui restrições e estará disponível para todos os clientes no checkout.',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'type' => [
            'label' => 'Tipo',
            'options' => [
                'unrestricted' => 'Sem restrições',
                'countries' => 'Limitar a países',
                'states' => 'Limitar a estados / municípios',
                'postcodes' => 'Limitar a CEPs',
            ],
        ],
        'country' => [
            'label' => 'País',
        ],
        'states' => [
            'label' => 'Estados',
        ],
        'countries' => [
            'label' => 'Estados',
        ],
        'postcodes' => [
            'label' => 'CEPs',
            'helper' => 'Liste cada CEP em uma nova linha. Suporta curingas como NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'type' => [
            'label' => 'Tipo',
            'options' => [
                'unrestricted' => 'Sem restrições',
                'countries' => 'Limitar a países',
                'states' => 'Limitar a estados / municípios',
                'postcodes' => 'Limitar a CEPs',
            ],
        ],
    ],
];
