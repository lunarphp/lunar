<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => "Associez des groupes de clients à cette méthode d'expédition pour déterminer sa disponibilité.",
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Tarifs d\'expédition',
        'actions' => [
            'create' => [
                'label' => 'Créer un tarif d\'expédition',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Tous les prix incluent la taxe, qui sera prise en compte lors du calcul du montant minimum.',
            'prices_excl_tax' => 'Tous les prix excluent la taxe, le montant minimum sera basé sur le sous-total du panier.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Méthode d\'expédition',
            ],
            'price' => [
                'label' => 'Prix',
            ],
            'prices' => [
                'label' => 'Réductions de prix',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Groupe de clients',
                        'placeholder' => 'N\'importe lequel',
                    ],
                    'currency_id' => [
                        'label' => 'Devise',
                    ],
                    'min_quantity' => [
                        'label' => 'Dépense min.',
                    ],
                    'price' => [
                        'label' => 'Prix',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Méthode d\'expédition',
            ],
            'price' => [
                'label' => 'Prix',
            ],
            'price_breaks_count' => [
                'label' => 'Réductions de prix',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Exclusions d\'expédition',
        'form' => [
            'purchasable' => [
                'label' => 'Produit',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Ajouter une liste d\'exclusion d\'expédition',
            ],
            'attach' => [
                'label' => 'Ajouter une liste d\'exclusion',
            ],
            'detach' => [
                'label' => 'Supprimer',
            ],
        ],
    ],
];
