<?php

return [
    'plural_label' => 'Réductions',
    'label' => 'Réduction',
    'form' => [
        'conditions' => [
            'heading' => 'Conditions',
        ],
        'buy_x_get_y' => [
            'heading' => 'Y pour le prix de X',
        ],
        'percentage_off' => [
            'heading' => 'Remise en pourcentage',
        ],
        'fixed_amount_off' => [
            'heading' => 'Remise à montant fixe',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'starts_at' => [
            'label' => 'Date de début',
        ],
        'ends_at' => [
            'label' => 'Date de fin',
        ],
        'priority' => [
            'label' => 'Priorité',
            'helper_text' => 'Les réductions avec une priorité plus élevée seront appliquées en premier.',
        ],
        'stop' => [
            'label' => 'Arrêter l\'application des autres réductions après celle-ci',
            'helper_text' => 'When this discount applies, any discount with a lower priority will be skipped. Give discounts different priorities to control the order they apply in.',
        ],
        'coupon' => [
            'label' => 'Coupon',
            'helper_text' => 'Entrez le coupon requis pour appliquer la réduction. Si laissé vide, il sera appliqué automatiquement.',
        ],
        'max_uses' => [
            'label' => 'Utilisations maximales',
            'helper_text' => 'Laissez vide pour des utilisations illimitées.',
        ],
        'max_uses_per_user' => [
            'label' => 'Utilisations maximales par utilisateur',
            'helper_text' => 'Laissez vide pour des utilisations illimitées.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Montant minimum du panier',
        ],
        'min_qty' => [
            'label' => 'Quantité de produits',
            'helper_text' => 'Définissez combien de produits qualifiants sont nécessaires pour appliquer la réduction.',
        ],
        'reward_qty' => [
            'label' => 'Nombre d\'articles gratuits',
            'helper_text' => 'Combien de chaque article sont réduits.',
        ],
        'max_reward_qty' => [
            'label' => 'Quantité maximale de récompense',
            'helper_text' => 'La quantité maximale de produits pouvant être réduits, quel que soit le critère.',
        ],
        'automatic_rewards' => [
            'label' => 'Ajouter automatiquement les récompenses',
            'helper_text' => 'Activez pour ajouter des produits de récompense lorsqu\'ils ne sont pas présents dans le panier.',
        ],
        'percentage' => [
            'label' => 'Pourcentage',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'status' => [
            'label' => 'Statut',
            'active' => [
                'label' => 'Actif',
            ],
            'pending' => [
                'label' => 'En attente',
            ],
            'expired' => [
                'label' => 'Expiré',
            ],
            'scheduled' => [
                'label' => 'Planifié',
            ],
        ],
        'type' => [
            'label' => 'Type',
        ],
        'starts_at' => [
            'label' => 'Date de début',
        ],
        'ends_at' => [
            'label' => 'Date de fin',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilité',
        ],
        'edit' => [
            'title' => 'Informations de base',
        ],
        'limitations' => [
            'label' => 'Limitations',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Collections',
            'description' => 'Sélectionnez les collections auxquelles cette réduction doit être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Associer une collection',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Customers',
            'description' => 'Select which customers this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Customer',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Marques',
            'description' => 'Sélectionnez les marques auxquelles cette réduction doit être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Associer une marque',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Produits',
            'description' => 'Sélectionnez les produits auxquels cette réduction doit être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter un produit',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Récompenses de produit',
            'description' => 'Sélectionnez les produits qui seront réduits s\'ils existent dans le panier et que les conditions ci-dessus sont remplies.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter un produit',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Conditions de produit',
            'description' => 'Sélectionnez les produits requis pour que la réduction soit appliquée.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter un produit',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'Collection Conditions',
            'description' => 'Select the collection conditions required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Condition',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Variantes de produit',
            'description' => 'Sélectionnez les variantes de produits auxquelles cette réduction doit être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter une variante de produit',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nom',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Option(s)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
