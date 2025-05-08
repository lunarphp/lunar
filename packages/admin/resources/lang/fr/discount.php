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
        'amount_off' => [
            'heading' => 'Montant de réduction',
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
            'options' => [
                'low' => [
                    'label' => 'Basse',
                ],
                'medium' => [
                    'label' => 'Moyenne',
                ],
                'high' => [
                    'label' => 'Haute',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Arrêter l\'application des autres réductions après celle-ci',
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
        'fixed_value' => [
            'label' => 'Valeur fixe',
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
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Actif',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'En attente',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Expiré',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
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
