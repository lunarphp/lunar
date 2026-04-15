<?php

return [
    'customer_groups' => [
        'title' => 'Groupes de clients',
        'actions' => [
            'attach' => [
                'label' => 'Associer un groupe de clients',
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
            'description' => 'Associez des groupes de clients à ce :type pour déterminer sa disponibilité.',
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
        'title' => 'Canaux',
        'actions' => [
            'attach' => [
                'label' => 'Planifier un autre canal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Activé',
                'helper_text_false' => 'Ce canal ne sera pas activé même si une date de début est présente.',
            ],
            'starts_at' => [
                'label' => 'Date de début',
                'helper_text' => 'Laissez vide pour être disponible à partir de n\'importe quelle date.',
            ],
            'ends_at' => [
                'label' => 'Date de fin',
                'helper_text' => 'Laissez vide pour être disponible indéfiniment.',
            ],
        ],
        'table' => [
            'description' => 'Déterminez quels canaux sont activés et planifiez la disponibilité.',
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
    'medias' => [
        'title' => 'Média',
        'title_plural' => 'Médias',
        'actions' => [
            'create' => [
                'label' => 'Créer un média',
            ],
            'view' => [
                'label' => 'Voir',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nom',
            ],
            'media' => [
                'label' => 'Image',
            ],
            'primary' => [
                'label' => 'Principal',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Image',
            ],
            'file' => [
                'label' => 'Fichier',
            ],
            'name' => [
                'label' => 'Nom',
            ],
            'primary' => [
                'label' => 'Principal',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'Créer une URL',
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
                'label' => 'Défaut',
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
                'label' => 'Défaut',
            ],
            'language' => [
                'label' => 'Langue',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Tarification des groupes de clients',
        'title_plural' => 'Tarification des groupes de clients',
        'table' => [
            'heading' => 'Tarification des groupes de clients',
            'description' => 'Associez un prix aux groupes de clients pour déterminer le prix du produit.',
            'empty_state' => [
                'label' => 'Aucune tarification de groupe de clients n\'existe.',
                'description' => 'Créez un prix de groupe de clients pour commencer.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Ajouter un prix de groupe de clients',
                    'modal' => [
                        'heading' => 'Créer un prix de groupe de clients',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Tarification',
        'title_plural' => 'Tarifications',
        'tab_name' => 'Réductions de prix',
        'table' => [
            'heading' => 'Réductions de prix',
            'description' => 'Réduisez le prix lorsqu\'un client achète en plus grande quantité.',
            'empty_state' => [
                'label' => 'Aucune réduction de prix n\'existe.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Ajouter une réduction de prix',
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
                'label' => 'Quantité minimum',
            ],
            'currency' => [
                'label' => 'Devise',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Prix',
                'helper_text' => 'Le prix d\'achat, avant réductions.',
            ],
            'customer_group_id' => [
                'label' => 'Groupe de clients',
                'placeholder' => 'Tous les groupes de clients',
                'helper_text' => 'Sélectionnez le groupe de clients auquel appliquer ce prix.',
            ],
            'min_quantity' => [
                'label' => 'Quantité minimum',
                'helper_text' => 'Sélectionnez la quantité minimum pour que ce prix soit disponible.',
                'validation' => [
                    'unique' => 'Le groupe de clients et la quantité minimum doivent être uniques.',
                ],
            ],
            'currency_id' => [
                'label' => 'Devise',
                'helper_text' => 'Sélectionnez la devise pour ce prix.',
            ],
            'compare_price' => [
                'label' => 'Prix de comparaison',
                'helper_text' => 'Le prix d\'origine ou PDSF, pour la comparaison avec son prix d\'achat.',
            ],
            'basePrices' => [
                'title' => 'Prix',
                'form' => [
                    'price' => [
                        'label' => 'Prix',
                        'helper_text' => 'Le prix d\'achat, avant réductions.',
                    ],
                    'compare_price' => [
                        'label' => 'Prix de comparaison',
                        'helper_text' => 'Le prix d\'origine ou PDSF, pour la comparaison avec son prix d\'achat.',
                    ],
                ],
                'tooltip' => 'Généré automatiquement en fonction des taux de change des devises.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Pourcentage',
            ],
            'tax_class' => [
                'label' => 'Classe de taxe',
            ],
        ],
    ],
    'values' => [
        'title' => 'Valeurs',
        'form' => [
            'name' => [
                'label' => 'Nom',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Nom',
            ],
            'position' => [
                'label' => 'Position',
            ],
        ],
    ],
];
