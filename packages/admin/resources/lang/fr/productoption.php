<?php

return [
    'label' => 'Option produit',
    'plural_label' => 'Options produit',
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'label' => [
            'label' => 'Étiquette',
        ],
        'handle' => [
            'label' => 'Gestionnaire',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'label' => [
            'label' => 'Étiquette',
        ],
        'handle' => [
            'label' => 'Gestionnaire',
        ],
    ],
    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Options de produit enregistrées',
                    ],
                ],
            ],
            'actions' => [
                'annuler' => [
                    'label' => 'Annuler',
                ],
                'enregistrer-options' => [
                    'label' => 'Enregistrer les options',
                ],
                'ajouter-option-partagee' => [
                    'label' => 'Ajouter une option partagée',
                    'form' => [
                        'option_produit' => [
                            'label' => 'Option produit',
                        ],
                        'pas_de_options_partages' => [
                            'label' => 'Aucune option partagée ne sont disponible.',
                        ],
                    ],
                ],
                'ajouter-option-restreinte' => [
                    'label' => 'Ajouter option',
                ],
            ],
            'liste-des-options' => [
                'vide' => [
                    'titre' => 'Aucune option produit configurée',
                    'description' => 'Ajoutez une option partagée ou réservée pour générer des variants.',
                ],
            ],
            'tableau-des-options' => [
                'titre' => 'Options produit',
                'configurer-les-options' => [
                    'label' => 'Configurer les options',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Option',
                    ],
                    'valeurs' => [
                        'label' => 'Valeurs',
                    ],
                ],
            ],
            'tableau-des-variants' => [
                'titre' => 'Variantes produit',
                'actions' => [
                    'creer' => [
                        'label' => 'Créer une variante',
                    ],
                    'editer' => [
                        'label' => 'Éditer',
                    ],
                    'supprimer' => [
                        'label' => 'Supprimer',
                    ],
                ],
                'vide' => [
                    'titre' => 'Aucune variante configurée',
                ],
                'table' => [
                    'nouvelle' => [
                        'label' => 'Nouveau',
                    ],
                    'option' => [
                        'label' => 'Option',
                    ],
                    'sku' => [
                        'label' => 'Sku',
                    ],
                    'prix' => [
                        'label' => 'Prix',
                    ],
                    'stock' => [
                        'label' => 'Stock',
                    ],
                ],
            ],
        ],
    ],
];
