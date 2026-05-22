<?php

return [

    'label' => 'Classe de taxe',

    'plural_label' => 'Classes de taxe',

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
    ],

    'delete' => [
        'error' => [
            'title' => 'Impossible de supprimer la classe de taxe',
            'body' => 'Cette classe de taxe a des variantes de produits associées et ne peut pas être supprimée.',
        ],
    ],

];
