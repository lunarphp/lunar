<?php

return [

    'label' => 'Clasă de taxe',

    'plural_label' => 'Clase de taxe',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'default' => [
            'label' => 'Implicită',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'default' => [
            'label' => 'Implicită',
        ],
    ],
    'delete' => [
        'error' => [
            'title' => 'Nu se poate șterge clasa de taxe',
            'body' => 'Această clasă de taxe are variante de produs asociate și nu poate fi ștearsă.',
        ],
    ],
];
