<?php

return [
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
        'accepts_pickup' => [
            'label' => 'Les clients peuvent retirer ici',
            'helper_text' => 'Proposer cet emplacement comme point de retrait lors du paiement.',
        ],
        'address' => [
            'label' => 'Adresse',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
        'accepts_pickup' => [
            'label' => 'Retrait',
        ],
        'fulfilments' => [
            'label' => 'Traitements',
        ],
    ],
];
