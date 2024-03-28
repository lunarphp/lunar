<?php

return [
    'customer_groups' => [
        'actions' => [
            'attach' => [
                'label' => 'Attacher groupe de clients',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nom',
            ],
            'enabled' => [
                'label' => 'Activé',
            ],
            'starts_at' => [
                'label' => 'Date de début',
            ],
            'ends_at' => [
                'label' => 'Date de fin',
            ],
            'visible' => [
                'label' => 'Visible',
            ],
            'purchasable' => [
                'label' => 'Achetable',
            ],
        ],
        'table' => [
            'description' => 'Associer des groupes de clients à ce produit pour déterminer sa disponibilité.',
            'name' => [
                'label' => 'Nom',
            ],
            'enabled' => [
                'label' => 'Activé',
            ],
            'starts_at' => [
                'label' => 'Date de début',
            ],
            'ends_at' => [
                'label' => 'Date de fin',
            ],
            'visible' => [
                'label' => 'Visible',
            ],
            'purchasable' => [
                'label' => 'Achetable',
            ],
        ],
    ],
    'channels' => [
        'actions' => [
            'attach' => [
                'label' => 'Programmer un autre canal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Activé',
                'helper_text_false' => 'Ce canal ne sera pas activé même si une date de début est présente.',
            ],
            'starts_at' => [
                'label' => 'Date de début',
                'helper_text' => 'Laisser vide pour être disponible à partir de n’importe quelle date.',
            ],
            'ends_at' => [
                'label' => 'Date de fin',
                'helper_text' => 'Laisser vide pour être disponible indéfiniment.',
            ],
        ],
        'table' => [
            'description' => 'Déterminer quels canaux sont activés et programmer la disponibilité.',
            'name' => [
                'label' => 'Nom',
            ],
            'enabled' => [
                'label' => 'Activé',
            ],
            'starts_at' => [
                'label' => 'Date de début',
            ],
            'ends_at' => [
                'label' => 'Date de fin',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'Créer URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Langue',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Par défaut',
            ],
            'language' => [
                'label' => 'Langue',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Par défaut',
            ],
            'language' => [
                'label' => 'Langue',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Tarification par groupe de clients',
        'title_plural' => 'Tarifications par groupe de clients',
        'table' => [
            'heading' => 'Tarification par groupe de clients',
            'description' => 'Associer le prix aux groupes de clients pour déterminer le prix du produit.',
            'empty_state' => [
                'label' => 'Aucune tarification par groupe de clients n’existe.',
                'description' => 'Créer un prix de groupe de clients pour commencer.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Ajouter Prix de groupe de clients',
                    'modal' => [
                        'heading' => 'Créer Prix de groupe de clients',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Tarification',
        'title_plural' => 'Tarifications',
        'tab_name' => 'Paliers de Prix',
        'table' => [
            'heading' => 'Paliers de Prix',
            'description' => 'Réduire le prix lorsque le client achète en plus grandes quantités.',
            'empty_state' => [
                'label' => 'Aucun palier de prix n’existe.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Ajouter Palier de Prix',
                ],
            ],
            'price' => [
                'label' => 'Prix',
            ],
            'customer_group' => [
                'label' => 'Groupe de clients',
                'placeholder' => 'Tous les groupes de clients',
            ],
            'min_quantity' => [
                'label' => 'Quantité Minimale',
            ],
            'currency' => [
                'label' => 'Devise',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Prix',
                'helper_text' => 'Le prix d’achat, avant réductions.',
            ],
            'customer_group_id' => [
                'label' => 'Groupe de clients',
                'placeholder' => 'Tous les groupes de clients',
                'helper_text' => 'Sélectionnez le groupe de clients auquel appliquer ce prix.',
            ],
            'min_quantity' => [
                'label' => 'Quantité Minimale',
                'helper_text' => 'Sélectionnez la quantité minimale pour laquelle ce prix sera disponible.',
                'validation' => [
                    'unique' => 'Le groupe de clients et la quantité minimale doivent être uniques.',
                ],
            ],
            'currency_id' => [
                'label' => 'Devise',
                'helper_text' => 'Sélectionnez la devise pour ce prix.',
            ],
            'compare_price' => [
                'label' => 'Prix de comparaison',
                'helper_text' => 'Le prix original ou le PRR, pour comparaison avec son prix d’achat.',
            ],
            'basePrices' => [
                'title' => 'Prix',
                'form' => [
                    'price' => [
                        'label' => 'Prix',
                        'helper_text' => 'Le prix d’achat, avant réductions.',
                    ],
                    'compare_price' => [
                        'label' => 'Prix de comparaison',
                        'helper_text' => 'Le prix original ou le PRR, pour comparaison avec son prix d’achat.',
                    ],
                ],
                'tooltip' => 'Généré automatiquement en fonction des taux de change des devises.',
            ],
        ],
    ],
];
