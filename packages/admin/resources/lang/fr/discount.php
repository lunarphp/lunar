<?php

return [
    'plural_label' => 'Réductions',
    'label' => 'Réduction',
    'form' => [
        'conditions' => [
            'heading' => 'Conditions',
        ],
        'buy_x_get_y' => [
            'heading' => 'Achetez X Obtenez Y',
        ],
        'amount_off' => [
            'heading' => 'Montant de la réduction',
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
            'helper_text' => 'Les réductions ayant une priorité plus élevée seront appliquées en premier.',
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
            'label' => 'Empêcher l’application d’autres réductions après celle-ci',
        ],
        'coupon' => [
            'label' => 'Coupon',
            'helper_text' => 'Entrez le coupon nécessaire pour que la réduction s’applique, si laissé vide elle s’appliquera automatiquement.',
        ],
        'max_uses' => [
            'label' => 'Nombre maximal d’utilisations',
            'helper_text' => 'Laisser vide pour un nombre illimité d’utilisations.',
        ],
        'max_uses_per_user' => [
            'label' => 'Nombre maximal d’utilisations par utilisateur',
            'helper_text' => 'Laisser vide pour un nombre illimité d’utilisations.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Montant minimum du panier',
        ],
        'min_qty' => [
            'label' => 'Quantité de produits',
            'helper_text' => 'Définissez le nombre de produits qualifiants nécessaires pour que la réduction s’applique.',
        ],
        'reward_qty' => [
            'label' => 'Nb. d’articles offerts',
            'helper_text' => 'Combien de chaque article sont remisés.',
        ],
        'max_reward_qty' => [
            'label' => 'Quantité de récompense maximale',
            'helper_text' => 'Le nombre maximum de produits pouvant être remisés, indépendamment des critères.',
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
                'label' => 'Programmé',
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
        'limitations' => [
            'label' => 'Limitations',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Collections',
            'description' => 'Sélectionnez les collections auxquelles cette réduction devrait être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Attacher Collection',
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
            'description' => 'Sélectionnez les marques auxquelles cette réduction devrait être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Attacher Marque',
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
            'description' => 'Sélectionnez les produits auxquels cette réduction devrait être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter Produit',
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
            'title' => 'Récompenses Produits',
            'description' => 'Sélectionnez les produits qui seront remisés s’ils existent dans le panier et si les conditions ci-dessus sont remplies.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter Produit',
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
            'title' => 'Conditions Produits',
            'description' => 'Sélectionnez les produits nécessaires pour que la réduction s’applique.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter Produit',
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
            'title' => 'Variantes de Produit',
            'description' => 'Sélectionnez les variantes de produit auxquelles cette réduction devrait être limitée.',
            'actions' => [
                'attach' => [
                    'label' => 'Ajouter Variante de Produit',
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
